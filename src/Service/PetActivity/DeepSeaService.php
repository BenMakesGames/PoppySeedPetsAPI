<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class DeepSeaService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;
    private $userQuestRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, UserQuestRepository $userQuestRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2);

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 18);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                    $activityLog = $this->foundAlgae($pet);
                else
                    $activityLog = $this->failedToUseSubmarine($pet);
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
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function failedToUseSubmarine(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to get the Submarine started, but forgot one of the steps, causing the whole thing to freak out and shut down :|', 'icons/activity-logs/confused');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

        return $activityLog;
    }

    private function foundAlgae(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took the Submarine out to sea, but didn\'t really get anywhere... some Algae got stuck to the hull, though, so there\'s that!', 'items/tool/submarine');

        $this->inventoryService->petCollectsItem('Algae', $pet, $pet->getName() . ' cleaned this off the Submarine.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

        return $activityLog;
    }

    private function foundSandOrSeaweed(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            if(mt_rand(1, 100) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the shelf sea using the Submarine, and found a Dino Skull!', 'items/tool/submarine');

                $this->inventoryService->petCollectsItem('Dino Skull', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine!', $activityLog);

                $pet->increaseEsteem(mt_rand(4, 8));

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
            }
            else
            {
                $loot = ArrayFunctions::pick_one([
                    'Seaweed', 'Silica Grounds', 'Fish', 'Scales'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the shelf sea using the Submarine. All they found was ' . $loot . '...', 'items/tool/submarine');

                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the shelf sea using the Submarine. (Pretty!)', 'items/tool/submarine');

            $pet->increaseEsteem(mt_rand(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fishedJellyFish(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the shelf sea using the Submarine. They caught a jellyfish while they were out there. (Yum! Jellyfish Jelly!)', 'items/tool/submarine');

            $this->inventoryService->petCollectsItem('Jellyfish Jelly', $pet, $pet->getName() . ' found this while exploring the shelf sea using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the shelf sea using the Submarine. A smack of jellyfish swam by; ' . $pet->getName() . ' watched in wonder...', 'items/tool/submarine');

            $pet->increaseEsteem(mt_rand(2, 4));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function exploredReef(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 50) === 1;

            if($lucky || mt_rand(1, 100) === 1)
            {
                $loot = 'Little Strongbox';

                if($lucky)
                    $period = '! Lucky~!';
                else
                    $period = '!';
            }
            else
            {
                $loot = ArrayFunctions::pick_one([
                    'Crown Coral', 'Fish', 'Sand Dollar'
                ]);

                $period = '.';
            }

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored a coral reef using the Submarine, and found ' . $loot . $period, 'items/tool/submarine');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while exploring a reef using the Submarine' . $period, $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' started exploring a coral reef using the Submarine, but was chased off by some sharks...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fishedHexactinellid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            if(mt_rand(1, 10) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used the Submarine and found a hexactinellid deep in the ocean... and took some of its Glass. (Rude?!)', 'items/tool/submarine');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used the Submarine and found a hexactinellid deep in the ocean... and took some of its Glass.', 'items/tool/submarine');

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            if($roll >= 24)
                $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' took this from a hexactinellid while exploring the depths of the ocean using the Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the deep sea using the Submarine, but didn\'t find anything...', 'icons/activity-logs/confused');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

    private function fightGiantSquid(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseFood(-1);

        $roll = mt_rand(1, 20 + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getDexterity()->getTotal()) + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            $tentacles = 2;

            if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, fought the squid, and stole a few Tentacles before it swam away!', 'items/tool/submarine');
                $roll += 5; // guaranteed to get at least 1 more tentacle
                $pet->getGuildMembership()->increaseReputation();
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Giant Squid while exploring the deep sea! They got out of the Submarine and fought it off, stealing a couple Tentacles!', 'items/tool/submarine');

            $pet->increaseEsteem(mt_rand(2, 4));

            if($roll >= 20) $tentacles++;
            if($roll >= 30) $tentacles++;

            for($i = 0; $i < $tentacles; $i++)
                $this->inventoryService->petCollectsItem('Tentacle', $pet, $pet->getName() . ' got this by defeating a Giant Squid in the deep sea!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
        }
        else if($pet->isInGuild(GuildEnum::HIGH_IMPACT))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $pet->increaseSafety(-mt_rand(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Giant Squid while exploring the deep sea! As a member of High Impact, they immediately stepped up to the challenge, but the squid attacked viciously, and ' . $pet->getName() . ' was forced to retreat...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Giant Squid while exploring the deep sea! They got away as quickly as they could!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function meetFriendlyWhale(Pet $pet)
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

        $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the deep sea, ' . $pet->getName() . ' watched a pod of whales go by! ' . $pet->getName() . ' swam and sang along with them for a while...', 'items/tool/submarine');

        $pet->increaseLove(mt_rand(2, 4));

        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::NATURE, PetSkillEnum::MUSIC ]);

        return $activityLog;
    }

    private function findSubmarineVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::FISH, false);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the deep sea using the Submarine, but it was too dark to see anything...', 'icons/activity-logs/confused');
        }

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            $loot = [
                ArrayFunctions::pick_one([ 'Liquid-hot Magma', 'Glass', 'Silica Grounds' ]),
                ArrayFunctions::pick_one([ 'Scales', 'Silica Grounds', 'Rock' ]),
            ];

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the deep sea using the Submarine, and found a submarine volcano! They looked around for a little while and scooped up some ' . ArrayFunctions::list_nice($loot) . ' before surfacing.', 'items/tool/submarine');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this near a submarine volcano using their Submarine.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the deep sea using the Submarine, and found a submarine volcano! They had to resurface before they could collect anything, though.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }

        if(mt_rand(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
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
                $pet->increaseSafety(-mt_rand(2, 4));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if(mt_rand(1, 20) === 1)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and I don\'t mean in a sexy way; ' . $pet->getName() . ' got a bit light-headed while cramped inside the Submarine.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and ' . $pet->getName() . ' got a bit light-headed while cramped inside the Submarine.');
            }
        }

        return $activityLog;
    }

    private function findSunkenShip(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() - ceil(($pet->getAlcohol() + $pet->getPsychedelic()) / 2));

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, true);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 50) === 1;

            $rareTreasure = null;
            $andMore = '!';

            if($lucky || mt_rand(1, 100) === 1)
            {
                $rareTreasure = ArrayFunctions::pick_one([
                    'Little Strongbox', 'Blackonite'
                ]);

                if($lucky)
                    $andMore = '; oh, and a ' . $rareTreasure . ', too! Lucky~!';
                else
                    $andMore = '; oh, and a ' . $rareTreasure . ', too!';
            }
            else if($roll >= 28)
            {
                if(mt_rand(1, 100) === 1)
                {
                    $rareTreasure = ArrayFunctions::pick_one([ 'Species Transmigration Serum', 'Yellow Bow' ]);
                    $andMore = '; oh, and a ' . $rareTreasure . ', too!';
                }
                else if(mt_rand(1, 10) === 1)
                {
                    $rareTreasure = 'Barnacles';
                    $andMore = '; oh, and some Barnacles, too!';
                }
                else
                {
                    $loot[] = ArrayFunctions::pick_one(['Silver Bar', 'Gold Ring']);
                }
            }

            $loot = [
                ArrayFunctions::pick_one([ 'Gold Bar', 'Gold Bar', 'Silver Bar', 'Silver Bar', 'Merchant Fish' ]),
                ArrayFunctions::pick_one([ 'Gold Bar', 'Silver Bar', 'Mermaid Egg', 'Scales', 'Fish', 'Seaweed', 'Captain\'s Log' ])
            ];

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the ocean using the Submarine, and found a sunken ship! Inside was ' . ArrayFunctions::list_nice($loot) . $andMore, 'items/tool/submarine');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine.', $activityLog);

            if($rareTreasure)
            {
                if($lucky)
                    $this->inventoryService->petCollectsItem($rareTreasure, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine! Lucky~!', $activityLog);
                else
                    $this->inventoryService->petCollectsItem($rareTreasure, $pet, $pet->getName() . ' found this in a sunken ship while exploring the ocean using the Submarine!', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::FISH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' started exploring a coral reef using the Submarine, but was chased off by some sharks...', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
        }

        return $activityLog;
    }

}
