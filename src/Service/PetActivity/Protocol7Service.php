<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\Enum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class Protocol7Service
{
    private $responseService;
    private $petExperienceService;
    private $inventoryService;
    private $transactionService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getIntelligence() + $pet->getScience() - $pet->getAlcohol();

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 16);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 5:
            case 6:
            case 7:
                $activityLog = $this->foundLayer01($pet);
                break;
            case 8:
            case 9:
            case 10:
                $activityLog = $this->foundLayer02($pet);
                break;
            case 11:
            case 12:
            case 13:
                $activityLog = $this->foundLayer03($pet);
                break;
            case 14:
            case 15:
            case 16:
                $activityLog = $this->foundLayer04($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = ceil($roll / 10);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, false);

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Project-E, but got lost.', 'icons/activity-logs/confused');
    }

    private function foundLayer01(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getIntelligence() + $pet->getScience());

        $success = $roll >= 10;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseEsteem(1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' saw a Garbage Collector in Project-E, and took one of the Pointers it was discarding.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' took this from a Garbage Collector in Project-E.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' saw a Garbage Collector passing by in Project-E, but couldn\'t catch up to it.', '');
        }
    }

    private function foundLayer02(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
            [
                'name' => 'a Trojan Horse',
                'loot' => [ 'Plastic' ]
            ],
            [
                'name' => 'a Clickjacker',
                'loot' => [ 'Browser Cookie' ],
            ],
            [
                'name' => 'an SQL Injection',
                'loot' => [ 'Finite State Machine' ]
            ]
        ]);

        $baddie = $monster['name'];
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 12;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseEsteem(1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 02 of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 02 of Project-E, but ' . $baddie . ' hijacked their session.', '');
        }
    }

    private function foundLayer03(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
            [
                'name' => 'a Keylogger',
                'loot' => [ 'Hash Table', 'Password' ]
            ],
            [
                'name' => 'a Rootkit',
                'loot' => [ 'Beans', 'Password' ],
            ],
            [
                'name' => 'a Boot Sector Virus',
                'loot' => [ 'Pointer', 'NUL' ]
            ]
        ]);

        $baddie = $monster['name'];
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 15;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 03 of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 03 of Project-E, but ' . $baddie . ' caused them to crash out.', '');
        }
    }

    private function foundLayer04(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        $monster = ArrayFunctions::pick_one([
            [
                'name' => 'a Slow Loris',
                'loot' => [ 'String', 'NUL' ]
            ],
            [
                'name' => 'a Man in the Middle',
                'loot' => [ 'Cryptocurrency Wallet', 'Cryptocurrency Wallet', 'Hash Table' ],
            ]
        ]);

        $baddie = $monster['name'];
        $loot = ArrayFunctions::pick_one($monster['loot']);
        $success = $roll >= 17;

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::PROTOCOL_7, $success);

        if($success)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 04 of Project-E, but defeated it, and took its ' . $loot . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 04 of Project-E, but their service was disrupted by ' . $baddie . '.', '');
        }
    }
}