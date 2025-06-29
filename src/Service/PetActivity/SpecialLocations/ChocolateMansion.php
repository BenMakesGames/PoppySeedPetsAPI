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


namespace App\Service\PetActivity\SpecialLocations;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetQuest;
use App\Entity\UserQuest;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\AdventureMath;
use App\Functions\EquipmentFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\Clock;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetQuestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChocolateMansion
{
    private const int QuestValuePatioOnly = 1;
    private const int QuestValueUpToGardens = 2;
    private const int QuestValueUpToFoyer = 3;
    private const int QuestValueAllExceptCellarAndAttic = 6;
    private const int QuestValueFullAccess = 8;

    public function __construct(
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly PetQuestRepository $petQuestRepository,
        private readonly EntityManagerInterface $em,
        private readonly FieldGuideService $fieldGuideService,
        private readonly Clock $clock
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills): void
    {
        $pet = $petWithSkills->getPet();

        EquipmentFunctions::destroyPetTool($this->em, $pet);

        $roomsAvailableQuest = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Chocolate Mansion Rooms', self::QuestValuePatioOnly);

        $petFurthestRoom = $this->petQuestRepository->findOrCreate($pet, 'Chocolate Mansion Furthest Room', self::QuestValueUpToFoyer);

        $maxRoom = min($roomsAvailableQuest->getValue(), $petFurthestRoom->getValue());

        $changes = new PetChanges($pet);

        $activityLog = match($this->rng->rngNextInt(1, $maxRoom)) {
            1 => $this->explorePatio($petWithSkills, $roomsAvailableQuest),
            2 => $this->exploreGardens($petWithSkills, $roomsAvailableQuest),
            3 => $this->exploreFoyer($petWithSkills, $petFurthestRoom),
            4 => $this->exploreParlor($petWithSkills, $roomsAvailableQuest),
            5 => $this->exploreMasterBedroom($petWithSkills, $roomsAvailableQuest),
            6 => $this->exploreStudy($petWithSkills, $roomsAvailableQuest),
            7 => $this->exploreCellar($petWithSkills),
            8 => $this->exploreAttic($petWithSkills),
            default => throw new \Exception('Invalid room number!'),
        };

        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Le Manoir de Chocolat', PetActivityLogTagEnum::Adventure ]))
        ;

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Le Manoir de Chocolat', $this->getEntryDescription($pet));

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function getEntryDescription(Pet $pet): string
    {
        return '%pet:' . $pet->getId() . '.name% used (and broke) their Chocolate Key to open the gates of le Manoir de Chocolat. ';
    }

    private function exploreAttic(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet) . 'They entered the attic, where an aggressive spectre was lying in wait! ';
        $loot = [];

        $combatRoll = $this->rng->rngNextInt(1, $this->rng->rngNextInt(16, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl(false)->getTotal());
        $magicRoll = $this->rng->rngNextInt(1, $this->rng->rngNextInt(16, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getArcana()->getTotal());

        if($combatRoll > $magicRoll)
        {
            if($combatRoll >= 20)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
                $expAmount = 3;
                $expStats = [ PetSkillEnum::Brawl ];

                $loot[] = 'Chocolate-stained Cloth';

                if($combatRoll >= 25)
                {
                    $loot[] = 'Chocolate Feather Bonnet';
                    $description .= '%pet:' . $pet->getId() . '.name% fought valiantly, and the spectre was forced to flee, dropping its Chocolate Feather Bonnet! %pet:' . $pet->getId() . '.name% took it, along with some Chocolate-stained Cloth from the attic.';
                }
                else
                {
                    $loot[] = 'Quintessence';
                    $description .= '%pet:' . $pet->getId() . '.name% fought valiantly, and the spectre was forced to flee, leaving a trail of Quintessence! %pet:' . $pet->getId() . '.name% collected it, along with some Chocolate-stained Cloth from the attic.';
                }
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
                $expAmount = 2;
                $expStats = [ PetSkillEnum::Brawl ];

                $description .= '%pet:' . $pet->getId() . '.name% was overwhelmed by the attack, and forced to flee!';
            }
        }
        else
        {
            if($magicRoll >= 20)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);
                $expAmount = 3;
                $expStats = [ PetSkillEnum::Arcana ];

                $loot[] = 'Chocolate-stained Cloth';

                if($magicRoll >= 25)
                {
                    $loot[] = 'Chocolate Feather Bonnet';
                    $description .= '%pet:' . $pet->getId() . '.name% evaded long enough to cast a banishing spell, and the spectre was forced to flee, dropping its Chocolate Feather Bonnet! %pet:' . $pet->getId() . '.name% took it, along with some Chocolate-stained Cloth from the attic.';
                }
                else
                {
                    $loot[] = 'Quintessence';
                    $description .= '%pet:' . $pet->getId() . '.name% evaded long enough to cast a banishing spell, and the spectre was forced to flee, leaving a trail of Quintessence! %pet:' . $pet->getId() . '.name% collected it, along with some Chocolate-stained Cloth from the attic.';
                }
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);
                $expAmount = 2;
                $expStats = [ PetSkillEnum::Arcana ];

                $description .= '%pet:' . $pet->getId() . '.name% tried to cast a banishing spell, but was overwhelmed by the attack, and forced to flee!';
            }

        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
        ;

        foreach($loot as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this from a spectre in the attic of le Manoir de Chocolat.', $activityLog);

        if(in_array('Chocolate Feather Bonnet', $loot))
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::FoundAChocolateFeatherBonnet, $activityLog);

        $this->petExperienceService->gainExp($pet, $expAmount, $expStats, $activityLog);

        return $activityLog;
    }

    private function exploreCellar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $description = $this->getEntryDescription($pet) . 'They entered the cellar, where a vampire was lying in wait! ';
        $loot = [];
        $tags = [];
        $icon = '';
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl(false)->getTotal());
        $extraInterestingness = 0;
        $deceivedTheVampire = false;

        if($pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::OTHER, true);
            $expAmount = 1;
            $expStats = [ PetSkillEnum::Stealth ];

            $item = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Blood Wine', 'Chocolate Wine',
            ]));

            $loot[] = $item;
            $tags[] = 'Stealth';
            $icon = 'icons/status-effect/bite-vampire';

            $description .= 'Fortunately, %pet:' . $pet->getId() . '.name%\'s vampire bite tricked the vampire into thinking they were the same sort of creatures! After apologizing, the vampire offered %pet:' . $pet->getId() . '.name% ' . $item->getNameWithArticle() . '. They accepted, and left as quickly as seemed polite.';
            $extraInterestingness = PetActivityLogInterestingness::UncommonActivity;

            $deceivedTheVampire = true;
        }
        else if($pet->getTool() && $pet->getTool()->isGrayscaling())
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::OTHER, true);
            $expAmount = 1;
            $expStats = [ PetSkillEnum::Stealth ];

            $item = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Blood Wine', 'Chocolate Wine',
            ]));

            $loot[] = $item;
            $tags[] = 'Stealth';

            $description .= 'Fortunately, %pet:' . $pet->getId() . '.name%\'s pale color tricked the vampire into thinking they were the same sort of creatures. After apologizing, the vampire offered %pet:' . $pet->getId() . '.name% ' . $item->getNameWithArticle() . '. They accepted, and left as quickly as seemed polite.';
            $extraInterestingness = PetActivityLogInterestingness::UncommonActivity;

            $deceivedTheVampire = true;
        }
        else if($pet->hasStatusEffect(StatusEffectEnum::Cordial))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(20, 40), PetActivityStatEnum::OTHER, true);
            $expAmount = 0;
            $expStats = [ ];

            $item = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Blood Wine', 'Chocolate Wine',
            ]));

            $loot[] = $item;

            $description .= 'Fortunately, the vampire was completely taken by %pet:' . $pet->getId() . '.name%\'s cordiality, and the two had a simply _wonderful_ time! The vampire offered %pet:' . $pet->getId() . '.name% ' . $item->getNameWithArticle() . '. They accepted, and left as quickly as seemed polite.';
            $extraInterestingness = PetActivityLogInterestingness::UncommonActivity;
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            $expAmount = 3;
            $expStats = [ PetSkillEnum::Brawl ];

            $loot[] = 'Chocolate Wine';
            $tags[] = 'Fighting';

            if($roll >= 25)
            {
                $loot[] = 'Chocolate Top Hat';
                $description .= '%pet:' . $pet->getId() . '.name% fought valiantly, and the vampire was forced to flee, dropping its Chocolate Top Hat! %pet:' . $pet->getId() . '.name% took it, along with a glass of Chocolate Wine from the cellar.';
            }
            else
            {
                $loot[] = 'Blood Wine';
                $description .= '%pet:' . $pet->getId() . '.name% fought valiantly, and the vampire was forced to flee! Afterwards, %pet:' . $pet->getId() . '.name% explored the cellar, and got a glass of Blood Wine, and Chocolate Wine.';
            }
        }
        else if($roll < 2 && $this->clock->getMonthAndDay() >= 1000 && $this->clock->getMonthAndDay() < 1200)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            $expAmount = 2;
            $expStats = [ PetSkillEnum::Brawl ];

            $pet->increaseSafety(-6);

            $description .= '%pet:' . $pet->getId() . '.name% was overwhelmed by the attack, and forced to flee... but not before getting bitten! (Uh oh!)';

            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::BittenByAVampire, 1);

            $tags[] = 'Fighting';
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            $expAmount = 2;
            $expStats = [ PetSkillEnum::Brawl ];

            $pet->increaseSafety(-3);

            $description .= '%pet:' . $pet->getId() . '.name% was overwhelmed by the attack, and forced to flee!';

            $tags[] = 'Fighting';
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
            ->setIcon($icon)
            ->addInterestingness($extraInterestingness)
        ;

        foreach($loot as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this from a vampire in the cellar of le Manoir de Chocolat.', $activityLog);

        $this->petExperienceService->gainExp($pet, $expAmount, $expStats, $activityLog);

        if($deceivedTheVampire)
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::DeceivedAVampire, $activityLog);

        return $activityLog;
    }

    private function exploreStudy(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        $searchRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());
        $success = $searchRoll >= 15;

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $success);

        if($success)
        {
            $book = $this->rng->rngNextFromArray([
                'Tiny Scroll of Resources', 'Le Chocolat', 'Scroll of Chocolate'
            ]);

            $description .= 'They entered a large study, and searched the shelves for anything interesting. Most of the books just broke into chocolate, but they did find a usable ' . $book . '!';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
            ;

            $this->inventoryService->petCollectsItem($book, $pet, $pet->getName() . ' found this in the study of le Manoir de Chocolat.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Stealth ], $activityLog);
        }
        else
        {
            $description .= 'They entered a large study, and searched the shelves for anything interesting, but every book they checked broke into pieces of chocolate!';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Stealth ], $activityLog);
        }

        $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, $pet->getName() . ' broke this off of a chocolate book in the study of le Manoir de Chocolat.', $activityLog);

        if($searchRoll > 2 && $quest->getValue() === self::QuestValueAllExceptCellarAndAttic)
        {
            $loot = null;

            $chemistryDescription = 'On one of the tables in the study, %pet:' . $pet->getId() . '.name% saw a table littered with notes, bottles, and chemistry equipment. ';

            $scienceRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

            if($scienceRoll <= 2)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(5, 10), PetActivityStatEnum::PROGRAM, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $chemistryDescription .= 'They weren\'t sure what to make of it.';
            }
            else if($scienceRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::PROGRAM, true);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
                $pet->increaseEsteem(8);

                $chemistryDescription .= 'They looked over the notes, and realized it was some kind of "dungeon puzzle". After following the instructions, they produced a liquid which puffed away immediately, and a strange "click" was heard from elsewhere in the mansion! (Dungeon puzzle!)';
                $quest->setValue(self::QuestValueFullAccess);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::PROGRAM, true);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $chemistryDescription .= 'They looked over the notes, and realized it was some kind of "dungeon puzzle", but after following the instructions, they were left with some Useless Fizz. (Must have messed something up!)';
                $loot = 'Useless Fizz';
            }

            $activityLog
                ->setEntry($activityLog->getEntry() . ' ' . $chemistryDescription)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Physics' ]))
            ;

            if($loot)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' created this while trying to do some SCIENCE.', $activityLog);
        }

        return $activityLog;
    }

    private function exploreMasterBedroom(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        $searchRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() * 2 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());
        $success = $searchRoll >= 20;

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $success);

        if($success)
        {
            $description .= 'They entered the master bedroom, and searched for anything interesting. Underneath the bed, they found a Chocolate Chest!';
            $loot = 'Chocolate Chest';
        }
        else
        {
            $description .= 'They entered the master bedroom, and searched for anything interesting, but just found a bunch of Chocolate-stained Cloth...';
            $loot = 'Chocolate-stained Cloth';
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
        ;

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this in the master bedroom of le Manoir de Chocolat.', $activityLog);

        $this->petExperienceService->gainExp($pet, $success ? 2 : 1, [ PetSkillEnum::Stealth ], $activityLog);

        if($searchRoll >= 25 && $quest->getValue() === self::QuestValueAllExceptCellarAndAttic)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Stealth ], $activityLog);
            $pet->increaseEsteem(8);

            $activityLog->setEntry($activityLog->getEntry() . ' While they were poking around, %pet:' . $pet->getId() . '.name% found a secret door with passages to the mansion\'s cellar and attic, and unlocked the doors to both before returning home!');

            $quest->setValue(self::QuestValueFullAccess);
        }

        return $activityLog;
    }

    private function exploreParlor(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet) . 'They entered a parlor, and looked around for anything interesting. ';

        $searchRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() * 2 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        if($this->rng->rngNextBool())
        {
            $loot = 'Chocolate Cue Ball';
            $description .= 'A chocolate pool table caught their attention; %pet:' . $pet->getId() . '.name% took one of its cue balls.';
        }
        else
        {
            $loot = 'Chocolate Wine';
            $description .= 'A bottle of Chocolate Wine caught their attention; %pet:' . $pet->getId() . '.name% grabbed it.';
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
        ;

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this in the parlor of le Manoir de Chocolat.', $activityLog);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Stealth ], $activityLog);

        if($searchRoll > 2 && $quest->getValue() === self::QuestValueAllExceptCellarAndAttic)
        {
            $loot = null;

            $musicDescription = 'The parlor also had a grand piano with some sheet music on it';

            $musicRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMusic()->getTotal());

            if($musicRoll <= 2)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(5, 10), PetActivityStatEnum::OTHER, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Music ], $activityLog);
                $musicDescription .= ', but the music was crazy-complicated, so %pet:' . $pet->getId() . '.name% left it alone.';
            }
            else if($musicRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::PROGRAM, true);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
                $musicDescription .= '. %pet:' . $pet->getId() . '.name% played it, producing a Music Note; when they had finished, a strange "click" was heard from elsewhere in the mansion. (A dungeon puzzle!?)';
                $pet->increaseEsteem(8);
                $quest->setValue(self::QuestValueFullAccess);
                $loot = 'Music Note';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::PROGRAM, true);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $musicDescription .= '. %pet:' . $pet->getId() . '.name% tried to play it, but couldn\'t get past a tricky part in the middle. They produced a Music Note, but ultimately had to give up, and move on.';
                $loot = 'Music Note';
            }

            $activityLog->setEntry($activityLog->getEntry() . ' ' . $musicDescription);

            if($loot)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' created this while playing a piano in the parlor of le Manoir de Chocolat.', $activityLog);
        }

        return $activityLog;
    }

    private function exploreFoyer(ComputedPetSkills $petWithSkills, PetQuest $petFurthestRoom): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);

        if($petFurthestRoom->getValue() === self::QuestValueUpToFoyer)
        {
            $petFurthestRoom->setValue(self::QuestValueFullAccess);
            $description .= 'They stepped into the mansion for the first time, and took a moment to marvel at the grand foyer... before immediately snooping around! While there, ';
        }
        else
        {
            $description .= 'They spent some time snooping around the foyer; while there, ';
        }

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());
        $difficulty = 16;

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $roll >= $difficulty);

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->rng->rngNextInt(3, 6));
            $description .= 'a chocolate chandelier fell, almost hitting %pet:' . $pet->getId() . '.name%! They grabbed a piece of its remains, and hightailed it out of there.';
            $comment = $pet->getName() . ' recovered this from a chandelier that tried to fall on them?!';
            $loot = 'Chocolate Bar';
        }
        else if($roll >= $difficulty)
        {
            if($this->rng->rngNextBool())
            {
                $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                    'Minor Scroll of Riches', 'Piece of Cetgueli\'s Map', 'Wings', 'Cast Net',
                    'Glowing Six-sided Die', 'Glowing Six-sided Die',
                ]));
                $description .= '%pet:' . $pet->getId() . '.name% opened the visor of a chocolate suit of armor, and found ' . $loot->getNameWithArticle() . ' inside!';
                $comment = $pet->getName() . ' found this in a chocolate suit of armor.';
            }
            else
            {
                $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                    'Pepperbox', 'Gold Bar', 'Warping Wand', 'XOR',
                    'Glowing Six-sided Die', 'Glowing Six-sided Die',
                ]));
                $description .= '%pet:' . $pet->getId() . '.name% noticed a chocolate grandfather clock had the wrong time, and fixed it. While they had it open, they found ' . $loot->getNameWithArticle() . ' inside!';
                $comment = $pet->getName() . ' found this in a chocolate grandfather clock.';
            }
        }
        else
        {
            $pet->increaseEsteem(-2);

            $loot = 'Chocolate Bar';

            if($this->rng->rngNextBool())
            {
                $description .= '%pet:' . $pet->getId() . '.name% tried to open the visor of a chocolate suit of armor, but accidentally broke a piece off, instead!';
                $comment = 'A piece broken off of a chocolate suit of armor.';
            }
            else
            {
                $description .= '%pet:' . $pet->getId() . '.name% noticed a chocolate grandfather clock had the wrong time, and tried to fix it, but accidentally broke a piece off, instead!';
                $comment = 'A piece broken off of a chocolate grandfather clock.';
            }
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Stealth' ]))
        ;

        $this->inventoryService->petCollectsItem($loot, $pet, $comment, $activityLog);

        $this->petExperienceService->gainExp($pet, $roll >= $difficulty ? 2 : 1, [ PetSkillEnum::Stealth ], $activityLog);

        return $activityLog;
    }

    private function exploreGardens(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);
        $usedMerit = false;
        $tags = [ 'Gathering' ];

        if($petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::GATHER, true);

            $description .= 'They explored the mansion\'s chocolate hedge maze, climbing over its walls and making directly for the center! ';
            $success = true;
        }
        else if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::GATHER, true);

            $description .= 'They explored the mansion\'s chocolate hedge maze, which was super-easy thanks to their Eidetic Memory! ';
            $usedMerit = true;
            $success = true;
        }
        else if($pet->hasMerit(MeritEnum::GOURMAND) && $pet->getFood() <= $pet->getStomachSize() * 3 / 4)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);

            $pet->increaseFood($this->rng->rngNextInt(4, 8));

            $description .= 'They explored the mansion\'s chocolate hedge maze, eating their way to the center! ';
            $usedMerit = true;
            $success = true;

            $tags[] = 'Eating';
            $tags[] = 'Gourmand';
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal());
            $success = $roll > 15;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, $success);

            if($success)
            {
                $description .= 'They explored the mansion\'s chocolate hedge maze, eventually finding their way to its center! ';
            }
            else
            {
                $description .= 'They explored the mansion\'s chocolate hedge maze, but got hopelessly lost...';
            }
        }

        if($success)
        {
            if($quest->getValue() === self::QuestValueUpToGardens)
            {
                $quest->setValue(self::QuestValueAllExceptCellarAndAttic);
                $description .= 'There was a chocolate fountain in the center; %pet:' . $pet->getId() . '.name% bottled some of the liquid. While they were doing so, they spotted a lever. Pulling it, a large \\*CLANK\\* was heard coming from the front of the house!';
            }
            else
            {
                $description .= 'There was a chocolate fountain in the center; %pet:' . $pet->getId() . '.name% bottled some of the liquid, and brought it home.';
            }

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description);

            $this->inventoryService->petCollectsItem('Chocolate Syrup', $pet, $pet->getName() . ' collected this from a chocolate fountain in the center of le Manoir de Chocolat\'s chocolate hedge maze.', $activityLog);
            $this->inventoryService->petCollectsItem('Chocolate Syrup', $pet, $pet->getName() . ' collected this from a chocolate fountain in the center of le Manoir de Chocolat\'s chocolate hedge maze.', $activityLog);
            if($this->rng->rngNextBool()) $this->inventoryService->petCollectsItem('Chocolate Syrup', $pet, $pet->getName() . ' collected this from a chocolate fountain in the center of le Manoir de Chocolat\'s chocolate hedge maze.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description);
        }

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));

        if($usedMerit)
            $activityLog->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit);

        return $activityLog;
    }

    private function explorePatio(ComputedPetSkills $petWithSkills, UserQuest $quest): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $description = $this->getEntryDescription($pet);
        $climbing = $this->rng->rngNextInt(1, 5) <= $petWithSkills->getClimbingBonus()->getTotal();

        if($quest->getValue() === 1)
        {
            $quest->setValue(2);
            $description .= 'They tried to enter the mansion\'s front door, but two, giant choco-steel bars blocked entry... ';

            if($climbing)
                $description .= 'so instead, they climbed up to the roof of the mansion, and broke off a couple of its chocolate shingles.';
            else
                $description .= 'while they were poking around, ';
        }
        else
        {
            if($climbing)
                $description .= 'They climbed the awning over the mansion\'s front patio, worked their way up to the roof, and broke off a couple of its chocolate singles.';
            else
                $description .= 'They explored the mansion\'s front patio; while they were poking around, ';
        }

        if($climbing)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, $pet->getName() . ' broke this "shingle" off the roof of le Manoir de Chocolat.', $activityLog);
            $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, $pet->getName() . ' broke this "shingle" off the root of le Manoir de Chocolat.', $activityLog);
        }
        else if($this->rng->rngNextBool())
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Cocoa Powder', 'Sugar',
            ]));

            $description .= 'they kicked up a pile of finely-ground ' . $loot->getName() . '. They came home covered in the stuff, and shook it off in the kitchen. It\'s... probably still good?';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);

            if($this->rng->rngNextInt(1, 3) === 1)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);

            if($this->rng->rngNextInt(1, 4) === 1)
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' got dusted with this while exploring the front patio of le Manoir de Chocolat.', $activityLog);
        }
        else
        {
            $combatRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal());
            $success = $combatRoll > 15;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            if($success)
            {
                $description .= 'a Chocolate Mastiff attacked them! %pet:' . $pet->getId() . '.name% fought back, and took a chunk out of the creature, forcing it to flee!';

                $loot = $this->rng->rngNextFromArray([
                    'Chocolate Bar',
                    'Orange Chocolate Bar', 'Orange Chocolate Bar',
                    'Spicy Chocolate Bar', 'Spicy Chocolate Bar'
                ]);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
                ;

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' broke this off of a Chocolate Mastiff at le Manoir de Chocolat.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Brawl ], $activityLog);
            }
            else
            {
                $description .= 'a Chocolate Mastiff spotted them and gave chase! %pet:' . $pet->getId() . '.name% was forced to flee!';

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $description)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $activityLog);
            }
        }

        return $activityLog;
    }
}
