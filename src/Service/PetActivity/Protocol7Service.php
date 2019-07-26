<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\Enum;
use App\Enum\MeritEnum;
use App\Functions\ArrayFunctions;
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
        $maxSkill = 10 + $pet->getIntelligence() + $pet->getComputer() - $pet->getWhack() - $pet->getJunk();

        if($maxSkill > 18) $maxSkill = 18;
        else if($maxSkill < 1) $maxSkill = 1;

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
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, ['intelligence', 'computer']);

        $pet->spendTime(\mt_rand(45, 75));

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Project-E, but got lost.');
    }

    private function foundLayer01(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        $baddie = ArrayFunctions::pick_one([ 'a Pop-under Ad', 'an overflowed buffer', 'a Spam E-mail' ]);

        $loot = ArrayFunctions::pick_one([ 'Pointer' ]);

        if($roll >= 10)
        {
        }
        else
        {
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 01 of Project-E, but their avatar was disrupted by ' . $baddie . '.');
        }
    }

    private function foundLayer02(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        $baddie = ArrayFunctions::pick_one([ 'a Trojan Horse', 'a Clickjacker', 'an SQL Injection' ]);

        if($roll >= 12)
        {
        }
        else
        {
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 02 of Project-E, but their avatar was disrupted by ' . $baddie . '.');
        }
    }

    private function foundLayer03(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        $baddie = ArrayFunctions::pick_one([ 'a Keylogger', 'a Rootkit', 'a Boot Sector Virus' ]);

        if($roll >= 15)
        {
        }
        else
        {
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' accessed Layer 03 of Project-E, but their avatar was disrupted by ' . $baddie . '.');
        }
    }

    private function foundLayer04(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        $baddie = ArrayFunctions::pick_one([ 'a Man in the Middle', 'a DDOS', 'a Slow Loris' ]);

        if($roll >= 20)
        {
        }
    }
}