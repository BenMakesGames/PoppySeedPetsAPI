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
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\FieldGuideService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class DeepSeaService
{
    public function __construct(
        private readonly ResponseService $responseService,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly HattierService $hattierService,
        private readonly FieldGuideService $fieldGuideService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2);

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 18);

        $roll = $this->rng->rngNextInt(1, $maxSkill);

        $changes = new PetChanges($pet);

        $activityLog = match($roll)
        {
            1, 2, 3, 4, 5 => $this->mostCommonAdventure($pet),
            6 => $this->foundAlgae($pet),
            7, 8 => $this->foundSandOrSeaweed($petWithSkills),
            9, 10 => $this->fishedJellyFish($petWithSkills),
            11, 12 => $this->exploredReef($petWithSkills),
            13 => $this->fishedHexactinellid($petWithSkills),
            14, 15 => $this->fightGiantSquid($petWithSkills),
            16 => $this->meetFriendlyWhale($pet),
            17 => $this->findSubmarineVolcano($petWithSkills),
            default => $this->findSunkenShip($petWithSkills),
        };

        $activityLog->setChanges($changes->compare($pet));

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function mostCommonAdventure(Pet $pet): PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::BEHATTED) && $this->rng->rngNextInt(1, 100) === 1)
        {
            $activityLog = $this->hattierService->petMaybeUnlockAura(
                $pet,
                'of Fish',
                ActivityHelpers::PetName($pet) . ' went on a quick tour of the shelf sea, and found themselves in a huge school of fish! It was strange, and beautiful; it really left an impression on ' . ActivityHelpers::PetName($pet) . '... and their hat!',
                ActivityHelpers::PetName($pet) . ' went on a quick tour of the shelf sea, and found themselves in a huge school of fish! It was strange, and beautiful...',
                ActivityHelpers::PetName($pet) . ' was dazzled by a huge school of fish on the shelf sea...'
            );

            if($activityLog)
            {
                $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine' ]));
                return $activityLog;
            }
        }

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            return $this->foundAlgae($pet);

        return $this->failedToUseSubmarine($pet);
    }

    private function failedToUseSubmarine(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to get the Submarine started, but forgot one of the steps, causing the whole thing to freak out and shut down :|', 'icons/activity-logs/confused')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

        return $activityLog;
    }

    private function foundAlgae(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took the Submarine out to sea, but didn\'t really get anywhere... some Algae got stuck to the hull, though, so there\'s that!', 'items/tool/submarine')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine' ]))
        ;

        $this->inventoryService->petCollectsItem('Algae', $pet, $pet->getName() . ' cleaned this off the Submarine.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

        return $activityLog;
    }

    private function foundSandOrSeaweed(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            if($this->rng->rngNextInt(1, 100) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine, and found a Dino Skull!', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
                ;

                $this->inventoryService->petCollectsItem('Dino Skull', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine!', $activityLog);

                $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            }
            else
            {
                $loot = $this->rng->rngNextFromArray([
                    'Seaweed', 'Silica Grounds', 'Fish', 'Scales'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. All they found was ' . $loot . '...', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. (Pretty!)', 'items/tool/submarine')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
            ;

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

    private function fishedJellyFish(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            if($this->rng->rngNextInt(1, 200) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. They spotted a lone Jelling Polyp while they were out there, and took it home!', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering' ]))
                    ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ;

                $pet->increaseEsteem(6);

                $this->inventoryService->petCollectsItem('Jelling Polyp', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. They caught a jellyfish while they were out there. (Yum! Jellyfish Jelly!)', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
                ;

                $this->inventoryService->petCollectsItem('Jellyfish Jelly', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. A smack of jellyfish swam by; ' . $pet->getName() . ' watched in wonder...', 'items/tool/submarine')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
            ;

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

    private function exploredReef(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $loot = $this->rng->rngNextFromArray([
                'Crown Coral',
                'Fish',
                'Sand Dollar',
                'Cucumber'
            ]);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% explored the Coral Reef using the Submarine, and found ' . ($loot === 'Cucumber' ? 'a sea ' : '') . $loot . '.')
                ->setIcon('items/tool/submarine')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]))
            ;

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the Coral Reef using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Coral Reef', '%pet:' . $pet->getId() . '.name% explored the Coral Reef using the Submarine.');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            if($this->rng->rngNextInt(1, 2) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring the Coral Reef using the Submarine, but was chased off by some Hammerheads...', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring the Coral Reef using the Submarine, but was chased off by a swarm of Jellyfish...', '');

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing' ]));

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Coral Reef', $activityLog->getEntry());

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

    private function fishedHexactinellid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing', 'Dark' ]))
            ;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            if($this->rng->rngNextInt(1, 10) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used the Submarine (and their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ') and found a hexactinellid deep in the ocean... and took some of its Glass. (Rude?!)', 'items/tool/submarine');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used the Submarine (and their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ') and found a hexactinellid deep in the ocean... and took some of its Glass.', 'items/tool/submarine');

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing', 'Dark' ]));

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            if($roll >= 24)
                $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine (and their ' . ActivityHelpers::SourceOfLight($petWithSkills) . '), but didn\'t find anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fishing', 'Dark' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

    private function fightGiantSquid(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseFood(-1);

        $roll = $this->rng->rngNextInt(1, 20 + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getDexterity()->getTotal()) + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $tentacles = 2;

            if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, fought the squid, and stole a few Tentacles before it swam away!', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fighting', 'Guild' ]))
                ;
                $roll += 5; // guaranteed to get at least 1 more tentacle
                $pet->getGuildMembership()->increaseReputation();
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! They got out of the Submarine and fought it off, stealing a couple Tentacles!', 'items/tool/submarine')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fighting' ]))
                ;
            }

            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            if($roll >= 20) $tentacles++;
            if($roll >= 30) $tentacles++;

            for($i = 0; $i < $tentacles; $i++)
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' got this by defeating a Giant Squid in the deep sea!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
        }
        else if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, but the squid attacked viciously, and ' . $pet->getName() . ' was forced to retreat...', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fighting', 'Guild' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! They got away as quickly as they could!', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Fighting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
        }

        return $activityLog;
    }

    private function meetFriendlyWhale(Pet $pet)
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the deep sea, %pet:' . $pet->getId() . '.name% watched a pod of whales go by! ' . $pet->getName() . ' swam and sang along with them for a while...', 'items/tool/submarine')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                'Submarine',
                PetActivityLogTagEnum::Location_The_Deep_Sea,
            ]))
        ;

        $pet->increaseLove($this->rng->rngNextInt(2, 4));

        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::NATURE, PetSkillEnum::MUSIC ], $activityLog);

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Whales', $activityLog->getEntry());

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::SING_WITH_WHALES, $activityLog);

        return $activityLog;
    }

    private function findSubmarineVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::FISH, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering', 'Dark' ]))
            ;
        }

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $loot = [
                $this->rng->rngNextFromArray([ 'Liquid-hot Magma', 'Glass', 'Silica Grounds' ]),
                $this->rng->rngNextFromArray([ 'Scales', 'Silica Grounds', 'Rock' ]),
            ];

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine (and their ' . ActivityHelpers::SourceOfLight($petWithSkills) . '), and found a submarine volcano! They looked around for a little while and scooped up some ' . ArrayFunctions::list_nice_sorted($loot) . ' before surfacing.', 'items/tool/submarine')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering', 'Dark' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this near a submarine volcano using their Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine (and their ' . ActivityHelpers::SourceOfLight($petWithSkills) . '), and found a submarine volcano! They had to resurface before they could collect anything, though.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering', 'Dark' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
        }

        if($this->rng->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was hot, but their ' . ActivityHelpers::SourceOfHeatProtection($petWithSkills) . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering', 'Dark', 'Heatstroke' ]))
                ;
            }
            else
            {
                $pet->increaseFood(-2);
                $pet->increaseSafety(-$this->rng->rngNextInt(2, 4));

                if($this->rng->rngNextInt(1, 20) === 1)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed while cramped inside the Submarine.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed while cramped inside the Submarine.');

                $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering', 'Dark', 'Heatstroke' ]));
            }
        }

        return $activityLog;
    }

    private function findSunkenShip(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - (int)ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 50) === 1;

            $rareTreasure = null;
            $rareTreasureEnchantment = null;
            $andMore = '!';

            if($lucky || $this->rng->rngNextInt(1, 100) === 1)
            {
                $rareTreasure = $this->rng->rngNextFromArray([
                    'Little Strongbox', 'Little Strongbox',
                    'Jolliest Roger', 'Jolliest Roger',
                    'Blackonite'
                ]);

                if($lucky)
                    $andMore = '; oh, and a ' . $rareTreasure . ', too! Lucky~!';
                else
                    $andMore = '; oh, and a ' . $rareTreasure . ', too!';
            }
            else if($roll >= 28)
            {
                if($this->rng->rngNextInt(1, 100) === 1)
                {
                    $rareTreasure = $this->rng->rngNextFromArray([ 'Species Transmigration Serum', 'Yellow Bow' ]);
                    $andMore = '; oh, and a ' . $rareTreasure . ', too!';
                }
                else if($this->rng->rngNextInt(1, 10) === 1)
                {
                    $rareTreasure = 'Barnacles';
                    $andMore = '; oh, and some Barnacles, too!';
                }
                else if($this->rng->rngNextInt(1, 10) === 1)
                {
                    $rareTreasure = 'No Right Turns';
                    $rareTreasureEnchantment = EnchantmentRepository::findOneByName($this->em, 'Seaweed-covered');
                    $andMore = '; oh, and a "No Right Turns" sign, too?';
                }
                else
                {
                    $loot[] = $this->rng->rngNextFromArray([ 'Silver Bar', 'Gold Ring' ]);
                }
            }

            $loot = [
                $this->rng->rngNextFromArray([ 'Gold Bar', 'Gold Bar', 'Silver Bar', 'Silver Bar', 'Merchant Fish' ]),
                $this->rng->rngNextFromArray([ 'Gold Bar', 'Silver Bar', 'Mermaid Egg', 'Scales', 'Fish', 'Seaweed', 'Captain\'s Log' ])
            ];

            $fleetDiscovery = '%pet:' . $pet->getId() . '.name% explored the ocean using the Submarine, and found a sunken ship!';

            $activityLog = $this->responseService->createActivityLog($pet, $fleetDiscovery . ' Inside was ' . ArrayFunctions::list_nice_sorted($loot) . $andMore, 'items/tool/submarine')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine.', $activityLog);

            if($rareTreasure)
            {
                if($lucky)
                    $this->inventoryService->petCollectsEnhancedItem($rareTreasure, $rareTreasureEnchantment, null, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine! Lucky~!', $activityLog);
                else
                    $this->inventoryService->petCollectsEnhancedItem($rareTreasure, $rareTreasureEnchantment, null, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine!', $activityLog);
            }

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Shipwrecked Fleet', $fleetDiscovery);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring a coral reef using the Submarine, but was chased off by some sharks...', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Submarine', 'Gathering' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

}
