<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\Enum;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class Protocol7Service
{
    private $responseService;
    private $petService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getIntelligence() + $pet->getComputer() - $pet->getAlcohol();

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 16);

        $roll = \mt_rand(1, $maxSkill);

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
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, [ PetSkillEnum::COMPUTER ]);
        $this->petService->spendTime($pet, \mt_rand(45, 60));

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Project-E, but got lost.', 'icons/activity-logs/confused');
    }

    private function foundLayer01(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());

        $monster = ArrayFunctions::pick_one([
            [
                'name' => 'an overflowed buffer',
                'loot' => [ 'Pointer' ]
            ],
            [
                'name' => 'a Pop-under Ad',
                'loot' => [ 'Pointer' ],
            ]
        ]);

        $baddie = $monster['name'];
        $loot = ArrayFunctions::pick_one($monster['loot']);

        $this->petService->spendTime($pet, \mt_rand(45, 75));

        if($roll >= 10)
        {
            $pet->increaseEsteem(1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);

            if(mt_rand(1, 10) === 1)
            {
                $moneys = mt_rand(2, 4);
                $pet->getOwner()->increaseMoneys($moneys);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 01 of Project-E, but defeated it, and took ' . $moneys . '~~m~~!', 'icons/activity-logs/moneys');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 01 of Project-E, but defeated it, and took its ' . $loot . '!', '');
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
                return $activityLog;
            }
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 01 of Project-E, but got hopelessly distracted by ' . $baddie . '.', '');
        }
    }

    private function foundLayer02(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());

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

        $this->petService->spendTime($pet, \mt_rand(45, 75));

        if($roll >= 12)
        {
            $pet->increaseEsteem(1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 02 of Project-E, but defeated it, and took its ' . $loot . '!', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 02 of Project-E, but ' . $baddie . ' hijacked their session.', '');
        }
    }

    private function foundLayer03(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());

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

        $this->petService->spendTime($pet, \mt_rand(45, 75));

        if($roll >= 15)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 03 of Project-E, but defeated it, and took its ' . $loot . '!', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 03 of Project-E, but ' . $baddie . ' caused them to crash out.', '');
        }
    }

    private function foundLayer04(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());

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

        $this->petService->spendTime($pet, \mt_rand(45, 75));

        if($roll >= 17)
        {
            $pet->increaseSafety(2);
            $pet->increaseEsteem(2);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was assaulted by ' . $baddie . ' in Layer 04 of Project-E, but defeated it, and took its ' . $loot . '!', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' defeated ' . $baddie . ', and took this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->increaseSafety(-1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 04 of Project-E, but their service was disrupted by ' . $baddie . '.', '');
        }
    }
}