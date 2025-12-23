<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\PetChanges;
use App\Model\SummoningScrollMonster;
use App\Model\SummoningScrollMonsterElementEnum;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class HouseMonsterService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly UserStatsService $userStatsRepository,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
        private readonly FieldGuideService $fieldGuideService
    )
    {
    }

    /**
     * @param Pet[] $petsAtHome
     */
    public function doFight(string $userSummonedDescription, array $petsAtHome, SummoningScrollMonster $monster): string
    {
        $user = $petsAtHome[0]->getOwner();

        $totalSkill = 0;
        /** @var string[] $petNames */ $petNames = [];
        /** @var Pet[] $unprotectedPets */ $unprotectedPets = [];
        /** @var string[] $unprotectedPetNames */ $unprotectedPetNames = [];
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($petsAtHome as $pet)
        {
            $petWithSkills = $pet->getComputedSkills();
            $totalSkill += $petWithSkills->getBrawl()->getTotal() + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getStamina()->getTotal()) + $petWithSkills->getDexterity()->getTotal();

            if($monster->element === SummoningScrollMonsterElementEnum::Fire)
            {
                if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
                    $totalSkill += 2;
                else
                {
                    $unprotectedPets[] = $pet;
                    $unprotectedPetNames[] = $pet->getName();
                }
            }
            else if($monster->element === SummoningScrollMonsterElementEnum::Electricity)
            {
                if($petWithSkills->getHasProtectionFromElectricity()->getTotal() > 0)
                    $totalSkill += 2;
                else
                {
                    $unprotectedPets[] = $pet;
                    $unprotectedPetNames[] = $pet->getName();
                }
            }
            else if($monster->element === SummoningScrollMonsterElementEnum::Darkness)
            {
                if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
                    $totalSkill += 2;
                else
                {
                    $unprotectedPets[] = $pet;
                    $unprotectedPetNames[] = $pet->getName();
                }
            }

            $petNames[] = $pet->getName();
            $petChanges[$pet->getId()] = new PetChanges($pet);
        }

        $roll = $this->rng->rngNextInt(max(20, 1 + ($totalSkill >> 1)), 20 + $totalSkill);

        $result = $userSummonedDescription . ', causing ' . $monster->nameWithArticle . ' to be summoned! ';

        $loot = $monster->minorRewards;

        $grab = $this->rng->rngNextFromArray([
            'grab', 'snag', 'take'
        ]);

        if($monster->fieldGuideEntry)
            $this->fieldGuideService->maybeUnlock($user, $monster->fieldGuideEntry, ArrayFunctions::list_nice($petNames) . ' fought ' . $monster->nameWithArticle . '!');

        if($roll >= 70)
        {
            $loot[] = $monster->majorReward;

            foreach($monster->minorRewards as $r)
                $loot[] = $r;

            $result .= ArrayFunctions::list_nice($petNames) . ' easily ' . (count($petsAtHome) === 1 ? 'dispatches' : 'dispatch') . ' the monster, taking its ' . ArrayFunctions::list_nice_sorted($loot) . '.';

            $exp = 5;
            $won = true;
        }
        else if($roll >= 50)
        {
            $loot[] = $monster->majorReward;
            $loot[] = $this->rng->rngNextFromArray($monster->minorRewards);

            $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'beats' : 'beat') . ' the monster back, and were rewarded with ' . ArrayFunctions::list_nice_sorted($loot) . '!';

            $exp = 5;
            $won = true;
        }
        else if($totalSkill < 30)
        {
            $petWithFairyGodmother = ArrayFunctions::find_one($petsAtHome, fn(Pet $p) => $p->hasMerit(MeritEnum::FAIRY_GODMOTHER));

            $won = $totalSkill >= 27 && $petWithFairyGodmother;

            if($won)
            {
                $loot[] = $monster->majorReward;

                $result .= 'It was a tough fight, and ' . ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' exhausted and about to give up when ' . $petWithFairyGodmother->getName() . '\'s Fairy Godmother flew in and dazzled the beast with flashy magic, distracting it long enough for your ' . (count($petsAtHome) === 1 ? 'pet' : 'pets') . ' to turn the fight in their favor, and collect ' . ArrayFunctions::list_nice_sorted($loot) . '!';
            }
            else
                $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' completely outmatched! At least they managed to ' . $grab. ' ' . ArrayFunctions::list_nice_sorted($loot) . '...';

            $exp = 2;
        }
        else
        {
            $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'fights' : 'fight') . ' their hardest, but ' . (count($petsAtHome) === 1 ? 'is' : 'are') . ' unable to defeat it! They were able to ' . $grab. ' ' . ArrayFunctions::list_nice_sorted($loot) . ', at least!';

            $exp = 3;
            $won = false;
        }

        foreach($petsAtHome as $pet)
        {
            if($won)
            {
                $pet
                    ->increaseSafety($this->rng->rngNextInt(4, 8))
                    ->increaseEsteem($this->rng->rngNextInt(6, 10))
                ;
            }
            else
            {
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));

                // you can't feel bad about yourself if you didn't even have a chance... right??
                if($totalSkill >= 40)
                    $pet->increaseEsteem(-$this->rng->rngNextInt(2, 4));
                else
                    $pet->increaseLove(-$this->rng->rngNextInt(2, 4)); // not very cool of you to summon the thing, then, though, I guess :P
            }
        }

        if(count($unprotectedPets) > 0)
        {
            if($monster->element === SummoningScrollMonsterElementEnum::Fire)
                $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' unprotected from the ' . $monster->name . '\'s flames, and got singed!';
            else if($monster->element === SummoningScrollMonsterElementEnum::Electricity)
                $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' unprotected from the ' . $monster->name . '\'s sparks, and got zapped!';
            else if($monster->element === SummoningScrollMonsterElementEnum::Darkness)
                $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' consumed by ' . $monster->name . '\'s darkness, and became terrified!';

            foreach($unprotectedPets as $pet)
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 12));
        }

        if($won)
        {
            $message = ArrayFunctions::list_nice($petNames) . ' got this by defeating ' . $monster->nameWithArticle . '.';
            $this->userStatsRepository->incrementStat($user, 'Won Against Something... Unfriendly');
        }
        else
        {
            $message = ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' defeated by ' . $monster->nameWithArticle . ', but managed to ' . $grab . ' this during the fight.';
            $this->userStatsRepository->incrementStat($user, 'Lost Against Something... Unfriendly');
        }

        foreach($loot as $item)
            $this->inventoryService->receiveItem($item, $user, $user, $message, LocationEnum::Home);

        foreach($petsAtHome as $pet)
        {
            $tags = [ 'Fighting' ];

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $result);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(5, 15), PetActivityStatEnum::HUNT, $won);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::Brawl ], $activityLog);

            $changes = $petChanges[$pet->getId()]->compare($pet);

            if($changes->level > 0)
                $tags[] = 'Level-up';

            $activityLog
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
                ->setChanges($changes);

            if($monster->petBadge && $won)
                PetBadgeHelpers::awardBadge($this->em, $pet, $monster->petBadge, $activityLog);
        }

        return $result;
    }
}