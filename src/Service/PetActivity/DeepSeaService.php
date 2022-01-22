<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Service\FieldGuideService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class DeepSeaService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private IRandom $squirrel3;
    private HattierService $hattierService;
    private FieldGuideService $fieldGuideService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        Squirrel3 $squirrel3, HattierService $hattierService, FieldGuideService $fieldGuideService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->squirrel3 = $squirrel3;
        $this->hattierService = $hattierService;
        $this->fieldGuideService = $fieldGuideService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2);

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 18);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $activityLog = $this->mostCommonAdventure($pet);
                break;
            case 6:
                $activityLog = $this->foundAlgae($pet);
                break;
            case 7:
            case 8:
                $activityLog = $this->foundSandOrSeaweed($petWithSkills);
                break;
            case 9:
            case 10:
                $activityLog = $this->fishedJellyFish($petWithSkills);
                break;
            case 11:
            case 12:
                $activityLog = $this->exploredReef($petWithSkills);
                break;
            case 13:
                $activityLog = $this->fishedHexactinellid($petWithSkills);
                break;
            case 14:
            case 15:
                $activityLog = $this->fightGiantSquid($petWithSkills);
                break;
            case 16:
                $activityLog = $this->meetFriendlyWhale($pet);
                break;
            case 17:
                $activityLog = $this->findSubmarineVolcano($petWithSkills);
                break;
            case 18:
                $activityLog = $this->findSunkenShip($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));

            if($activityLog->getChanges()->level > 0)
                $activityLog->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]));
        }

        if($this->squirrel3->rngNextInt(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function mostCommonAdventure(Pet $pet): PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::BEHATTED) && $this->squirrel3->rngNextInt(1, 100) === 1)
        {
            $activityLog = $this->hattierService->petMaybeUnlockAura(
                $pet,
                'of Fish',
                ActivityHelpers::PetName($pet) . ' went on a quick tour of the shelf sea, and found themselves in a huge school of fish! It was strange, and beautiful; it really left an impression on ' . ActivityHelpers::PetName($pet) . '... and their hat!',
                ActivityHelpers::PetName($pet) . ' went on a quick tour of the shelf sea, and found themselves in a huge school of fish! It was strange, and beautiful...',
                ActivityHelpers::PetName($pet) . ' was dazzled by a huge school of fish on the shelf sea...'
            );

            if($activityLog)
                return $activityLog;
        }

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            return $this->foundAlgae($pet);

        return $this->failedToUseSubmarine($pet);
    }

    private function failedToUseSubmarine(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to get the Submarine started, but forgot one of the steps, causing the whole thing to freak out and shut down :|', 'icons/activity-logs/confused');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

        return $activityLog;
    }

    private function foundAlgae(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took the Submarine out to sea, but didn\'t really get anywhere... some Algae got stuck to the hull, though, so there\'s that!', 'items/tool/submarine');

        $this->inventoryService->petCollectsItem('Algae', $pet, $pet->getName() . ' cleaned this off the Submarine.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

        return $activityLog;
    }

    private function foundSandOrSeaweed(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            if($this->squirrel3->rngNextInt(1, 100) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine, and found a Dino Skull!', 'items/tool/submarine');

                $this->inventoryService->petCollectsItem('Dino Skull', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine!', $activityLog);

                $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
            }
            else
            {
                $loot = $this->squirrel3->rngNextFromArray([
                    'Seaweed', 'Silica Grounds', 'Fish', 'Scales'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. All they found was ' . $loot . '...', 'items/tool/submarine');

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. (Pretty!)', 'items/tool/submarine');

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fishedJellyFish(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. They caught a jellyfish while they were out there. (Yum! Jellyfish Jelly!)', 'items/tool/submarine');

            $this->inventoryService->petCollectsItem('Jellyfish Jelly', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the shelf sea using the Submarine. A smack of jellyfish swam by; ' . $pet->getName() . ' watched in wonder...', 'items/tool/submarine');

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function exploredReef(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 50) === 1;

            if($lucky || $this->squirrel3->rngNextInt(1, 100) === 1)
            {
                $loot = 'Little Strongbox';

                if($lucky)
                    $period = '! Lucky~!';
                else
                    $period = '!';
            }
            else
            {
                $loot = $this->squirrel3->rngNextFromArray([
                    'Crown Coral', 'Fish', 'Sand Dollar'
                ]);

                $period = '.';
            }

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the Coral Reef using the Submarine, and found ' . $loot . $period, 'items/tool/submarine');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the Coral Reef using the Submarine' . $period, $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Coral Reef', '%pet:' . $pet->getId() . '.name% explored the Coral Reef using the Submarine.');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring the Coral Reef using the Submarine, but was chased off by some Hammerheads...', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring the Coral Reef using the Submarine, but was chased off by a swarm of Jellyfish...', '');

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Coral Reef', $activityLog->getEntry());

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fishedHexactinellid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            if($this->squirrel3->rngNextInt(1, 10) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used the Submarine and found a hexactinellid deep in the ocean... and took some of its Glass. (Rude?!)', 'items/tool/submarine');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used the Submarine and found a hexactinellid deep in the ocean... and took some of its Glass.', 'items/tool/submarine');

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            if($roll >= 24)
                $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, but didn\'t find anything...', 'icons/activity-logs/confused');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fightGiantSquid(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseFood(-1);

        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getDexterity()->getTotal()) + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $tentacles = 2;

            if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, fought the squid, and stole a few Tentacles before it swam away!', 'items/tool/submarine');
                $roll += 5; // guaranteed to get at least 1 more tentacle
                $pet->getGuildMembership()->increaseReputation();
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! They got out of the Submarine and fought it off, stealing a couple Tentacles!', 'items/tool/submarine');

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            if($roll >= 20) $tentacles++;
            if($roll >= 30) $tentacles++;

            for($i = 0; $i < $tentacles; $i++)
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' got this by defeating a Giant Squid in the deep sea!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
        }
        else if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, but the squid attacked viciously, and ' . $pet->getName() . ' was forced to retreat...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Giant Squid while exploring the deep sea! They got away as quickly as they could!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function meetFriendlyWhale(Pet $pet)
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the deep sea, %pet:' . $pet->getId() . '.name% watched a pod of whales go by! ' . $pet->getName() . ' swam and sang along with them for a while...', 'items/tool/submarine');

        $pet->increaseLove($this->squirrel3->rngNextInt(2, 4));

        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::NATURE, PetSkillEnum::MUSIC ]);

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Whales', $activityLog->getEntry());

        return $activityLog;
    }

    private function findSubmarineVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::FISH, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused');
        }

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $loot = [
                $this->squirrel3->rngNextFromArray([ 'Liquid-hot Magma', 'Glass', 'Silica Grounds' ]),
                $this->squirrel3->rngNextFromArray([ 'Scales', 'Silica Grounds', 'Rock' ]),
            ];

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, and found a submarine volcano! They looked around for a little while and scooped up some ' . ArrayFunctions::list_nice($loot) . ' before surfacing.', 'items/tool/submarine');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this near a submarine volcano using their Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the deep sea using the Submarine, and found a submarine volcano! They had to resurface before they could collect anything, though.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-2);
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));

                if($this->squirrel3->rngNextInt(1, 20) === 1)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed while cramped inside the Submarine.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed while cramped inside the Submarine.');
            }
        }

        return $activityLog;
    }

    private function findSunkenShip(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, true);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 50) === 1;

            $rareTreasure = null;
            $andMore = '!';

            if($lucky || $this->squirrel3->rngNextInt(1, 100) === 1)
            {
                $rareTreasure = $this->squirrel3->rngNextFromArray([
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
                if($this->squirrel3->rngNextInt(1, 100) === 1)
                {
                    $rareTreasure = $this->squirrel3->rngNextFromArray([ 'Species Transmigration Serum', 'Yellow Bow' ]);
                    $andMore = '; oh, and a ' . $rareTreasure . ', too!';
                }
                else if($this->squirrel3->rngNextInt(1, 10) === 1)
                {
                    $rareTreasure = 'Barnacles';
                    $andMore = '; oh, and some Barnacles, too!';
                }
                else
                {
                    $loot[] = $this->squirrel3->rngNextFromArray(['Silver Bar', 'Gold Ring']);
                }
            }

            $loot = [
                $this->squirrel3->rngNextFromArray([ 'Gold Bar', 'Gold Bar', 'Silver Bar', 'Silver Bar', 'Merchant Fish' ]),
                $this->squirrel3->rngNextFromArray([ 'Gold Bar', 'Silver Bar', 'Mermaid Egg', 'Scales', 'Fish', 'Seaweed', 'Captain\'s Log' ])
            ];

            $fleetDiscovery = '%pet:' . $pet->getId() . '.name% explored the ocean using the Submarine, and found a sunken ship!';

            $activityLog = $this->responseService->createActivityLog($pet, $fleetDiscovery . ' Inside was ' . ArrayFunctions::list_nice($loot) . $andMore, 'items/tool/submarine');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine.', $activityLog);

            if($rareTreasure)
            {
                if($lucky)
                    $this->inventoryService->petCollectsItem($rareTreasure, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine! Lucky~!', $activityLog);
                else
                    $this->inventoryService->petCollectsItem($rareTreasure, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine!', $activityLog);
            }

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Shipwrecked Fleet', $fleetDiscovery);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started exploring a coral reef using the Submarine, but was chased off by some sharks...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

}
