<?php
namespace App\Service\PetActivity;

use App\Entity\GreenhousePlant;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class GreenhouseAdventureService
{
    private $petService;
    private $responseService;
    private $inventoryService;

    function __construct(PetService $petService, ResponseService $responseService, InventoryService $inventoryService)
    {
        $this->petService = $petService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
    }

    public function adventure(Pet $pet, GreenhousePlant $plant): PetActivityLog
    {
        $skill = 10 + $pet->getNature() + $pet->getDexterity();
        $skill = NumberFunctions::constrain($skill, 10, 15);

        $roll = mt_rand(1, $skill);

        $this->petService->gainAffection($pet, 1);

        if($roll <= 8)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' had fun helping you harvest the ' . $plant->getPlant()->getName() . '.', 'ui/affection');
        }
        else if($roll <= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' had fun helping you harvest the ' . $plant->getPlant()->getName() . ', and found a Crooked Stick!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' found this while helping you harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' had fun helping you harvest the ' . $plant->getPlant()->getName() . ', and found a Chanterelle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Chanterelle', $pet, $pet->getName() . ' found this while helping you harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 13)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' had fun helping you harvest the ' . $plant->getPlant()->getName() . ', and found some Witch-hazel!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Witch-hazel', $pet, $pet->getName() . ' found this while helping you harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' had fun helping you harvest the ' . $plant->getPlant()->getName() . ', and found a Weird Beetle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Weird Beetle', $pet, $pet->getName() . ' found this while helping you harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }

        return $activityLog;
    }
}
