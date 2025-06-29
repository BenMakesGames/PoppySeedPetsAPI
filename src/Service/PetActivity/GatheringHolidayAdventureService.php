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

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GatheringHolidayEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Exceptions\UnreachableException;
use App\Functions\AdventureMath;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class GatheringHolidayAdventureService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private const array HolidayTags = [
        GatheringHolidayEnum::Easter->value => 'Easter',
        GatheringHolidayEnum::SaintPatricks->value => 'St. Patrick\'s',
        GatheringHolidayEnum::LunarNewYear->value => 'Lunar New Year'
    ];

    public function adventure(ComputedPetSkills $petWithSkills, GatheringHolidayEnum $holiday): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 21);

        $roll = $this->rng->rngNextInt(1, $maxSkill);

        $changes = new PetChanges($pet);

        $activityLog = match($roll)
        {
            1, 2, 3 => $this->goSearching($petWithSkills, $holiday, 'just outside the house', 0, 1, 0, 1),
            4, 5 => $this->goSearching($petWithSkills, $holiday, 'on the bank of a small stream', 0, 2, 10, 1),
            6, 7, 8 => $this->goSearching($petWithSkills, $holiday, 'in an Abandoned Quarry', 0, 2, 20, 1),
            9 => $this->goSearching($petWithSkills, $holiday, 'near a Waterfall Basin', 0, 2, 10, 1),
            10 => $this->goSearching($petWithSkills, $holiday, 'near the Plaza fountain', 0, 0, 10, 2),
            11, 12, 13 => $this->goSearching($petWithSkills, $holiday, 'at the beach', 1, 2, 20, 2),
            14 => $this->goSearching($petWithSkills, $holiday, 'in an Overgrown Garden', 0, 4, 30, 2),
            15, 16 => $this->goSearching($petWithSkills, $holiday, 'in an Old Iron Mine', 0, 2, 50, 2, true),
            17, 18 => $this->goSearching($petWithSkills, $holiday, 'in the Micro-Jungle', 1, 3, 40, 2, false, true),
            19, 20 => $this->goSearching($petWithSkills, $holiday, 'around the island\'s Volcano', 1, 5, 50, 3, false, true),
            default => $this->goSearching($petWithSkills, $holiday, 'in a huge cave', 1, 3, 60, 3, true),
        };

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Special Event', self::HolidayTags[$holiday->value] ]))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private static function searchingFor(GatheringHolidayEnum $holiday, bool $plural): string
    {
        return match ($holiday)
        {
            GatheringHolidayEnum::Easter => $plural ? 'plastic eggs' : 'plastic egg',
            GatheringHolidayEnum::SaintPatricks => $plural ? 'clovers' : 'clover',
            GatheringHolidayEnum::LunarNewYear => $plural ? 'moneys envelopes' : 'moneys envelope',
            default => throw new UnreachableException()
        };
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function goSearching(ComputedPetSkills $petWithSkills, GatheringHolidayEnum $holiday, string $where, int $minEggs, int $maxEggs, int $encounterChance, int $experience, bool $dark = false, bool $hot = false): PetActivityLog
    {
        if($holiday === GatheringHolidayEnum::Easter)
        {
            if($this->rng->rngNextInt(1, 100) <= $encounterChance && date('l') !== 'Friday')
                return $this->getAttacked($petWithSkills, $maxEggs);
        }

        $pet = $petWithSkills->getPet();

        if($hot)
        {
            if(!$petWithSkills->getHasProtectionFromHeat()->getTotal() > 0 && $this->rng->rngNextInt(1, 10) > $petWithSkills->getStamina()->getTotal())
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));
                return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went looking for '. GatheringHolidayAdventureService::searchingFor($holiday, true) . ' ' . $where . ', but it was way too hot; they couldn\'t find anything before they had to leave :(');
            }
        }

        $message = $pet->getName() . ' went looking for '. GatheringHolidayAdventureService::searchingFor($holiday, true) . ' ' . $where;

        if($dark)
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
            {
                $numItems = $this->rng->rngNextInt($minEggs + 1, $maxEggs + $this->rng->rngNextInt(1, 2));

                $message .= '. It was really dark, but that didn\'t pose a problem for ' . $pet->getName() . ', who found ' . $numItems . ' ' . GatheringHolidayAdventureService::searchingFor($holiday, $numItems !== 1) . '!';
            }
            else
            {
                $numItems = $this->rng->rngNextInt($minEggs, $maxEggs);

                if($numItems === 0)
                    $message .= ', but it was really dark, and they weren\'t able to find any :(';
                else
                    $message .= '. It was really dark, but they were still able to find ' . $numItems . '!';
            }
        }
        else
        {
            $numItems = $this->rng->rngNextInt($minEggs, $maxEggs);

            if($numItems === 0)
                $message .= ', but wasn\'t able to find any :(';
            else
                $message .= ', and found ' . $numItems . '!';
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $numItems > 0);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        $this->petExperienceService->gainExp($pet, $experience, [ PetSkillEnum::Nature ], $activityLog);

        if($holiday === GatheringHolidayEnum::Easter)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $r = random_int(1, 100);

                if($r <= 2) // 2%
                    $egg = 'Pink Plastic Egg';
                else if($r <= 11) // 9%
                    $egg = 'Yellow Plastic Egg';
                else // 89%
                    $egg = 'Blue Plastic Egg';

                $this->inventoryService->petCollectsItem($egg, $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog)
                    ->setLockedToOwner($egg !== 'Blue Plastic Egg')
                ;
            }

            if($numItems > 0)
                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::FoundAPlasticEgg, $activityLog);
        }
        else if($holiday === GatheringHolidayEnum::SaintPatricks)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $item = $this->inventoryService->petCollectsItem('1-leaf Clover', $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog);

                // it might have been insta-eaten, so check that it exists, first:
                if($item) $item->setLockedToOwner(true);
            }

            if($numItems > 0)
                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::FoundOneCloverLeaf, $activityLog);
        }
        else if($holiday === GatheringHolidayEnum::LunarNewYear)
        {
            for($i = 0; $i < $numItems; $i++)
            {
                $this->inventoryService->petCollectsItem('Red Envelope', $pet, $pet->getName() . ' found this ' . $where . '!', $activityLog)
                    ->setLockedToOwner(true);
            }
        }
        else
            throw new UnreachableException("Oops! Ben forgot to code holiday adventure logic for {$holiday}!");

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function getAttacked(ComputedPetSkills $petWithSkills, int $level): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $difficulty = 10 + $level * 3;

        $gotBehattingScrollThisEaster = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Easter ' . date('Y') . ' Behatting Scroll', false);

        if($gotBehattingScrollThisEaster->getValue() === false && $level >= 2)
        {
            $loot = 'Behatting Scroll';
        }
        else
        {
            $loot = $this->rng->rngNextFromArray([
                'Blue Plastic Egg',
                'Yellow Plastic Egg',
                $this->rng->rngNextFromArray([ 'Quintessence', 'Dark Scales' ]),
                $this->rng->rngNextFromArray([ 'Fluff', 'Talon' ]),
                $this->rng->rngNextFromArray([ 'Matzah Bread', 'Fish' ]),
            ]);
        }

        $skillCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal());

        $adjective = $this->rng->rngNextFromArray([
            'horrible', 'crazy', 'mutant', 'disturbing', 'frickin\' weird', 'bananas'
        ]);

        if($skillCheck >= $difficulty)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $pet->increaseEsteem($level);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing, but was able to defeat it!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + $level * 3)
            ;

            $this->petExperienceService->gainExp($pet, $level, [ PetSkillEnum::Brawl ], $activityLog);

            $newItem = $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated some kind of ' . $adjective . ', fish-rabbit hybrid thing, and got this!', $activityLog);

            // it might get eaten by the pet, immediately!
            if($newItem)
                $newItem->setLockedToOwner($loot === 'Behatting Scroll' || $loot === 'Yellow Plastic Egg');

            if($loot === 'Behatting Scroll')
            {
                $gotBehattingScrollThisEaster->setValue(true);
                $activityLog->addInterestingness(PetActivityLogInterestingness::RareActivity);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $pet->increaseSafety(-$level);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was attacked by some kind of ' . $adjective . ', fish-rabbit hybrid thing! ' . $pet->getName() . ' couldn\'t land a single attack, and ran away!');

            $this->petExperienceService->gainExp($pet, (int)ceil($level / 2), [ PetSkillEnum::Brawl ], $activityLog);

            return $activityLog;
        }
    }
}
