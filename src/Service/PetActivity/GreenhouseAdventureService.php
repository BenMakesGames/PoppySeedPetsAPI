<?php
namespace App\Service\PetActivity;

use App\Entity\GreenhousePlant;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class GreenhouseAdventureService
{
    private $petService;
    private $responseService;
    private $inventoryService;
    private $squirrel3;

    function __construct(
        PetService $petService, ResponseService $responseService, InventoryService $inventoryService,
        Squirrel3 $squirrel3
    )
    {
        $this->petService = $petService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->squirrel3 = $squirrel3;
    }

    public function adventure(ComputedPetSkills $petWithSkills, GreenhousePlant $plant): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getDexterity()->getTotal();
        $skill = NumberFunctions::clamp($skill, 10, 15);

        $roll = $this->squirrel3->rngNextInt(1, $skill);

        $changes = new PetChanges($pet);

        $this->petService->gainAffection($pet, 1);

        if($roll <= 8)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had fun helping %user:' . $pet->getOwner()->getId() . '.name% harvest the ' . $plant->getPlant()->getName() . '.', 'ui/affection');
        }
        else if($roll <= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had fun helping %user:' . $pet->getOwner()->getId() . '.name% harvest the ' . $plant->getPlant()->getName() . ', and found a Crooked Stick!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had fun helping %user:' . $pet->getOwner()->getId() . '.name% harvest the ' . $plant->getPlant()->getName() . ', and found a Chanterelle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Chanterelle', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 13)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had fun helping %user:' . $pet->getOwner()->getId() . '.name% harvest the ' . $plant->getPlant()->getName() . ', and found some Witch-hazel!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Witch-hazel', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had fun helping %user:' . $pet->getOwner()->getId() . '.name% harvest the ' . $plant->getPlant()->getName() . ', and found a Weird Beetle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Weird Beetle', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' harvest the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }
}
