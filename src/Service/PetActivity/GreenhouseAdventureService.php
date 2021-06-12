<?php
namespace App\Service\PetActivity;

use App\Entity\GreenhousePlant;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class GreenhouseAdventureService
{
    private $responseService;
    private $inventoryService;
    private $squirrel3;
    private PetExperienceService $petExperienceService;
    private HattierService $hattierService;
    private EnchantmentRepository $enchantmentRepository;

    function __construct(
        ResponseService $responseService, InventoryService $inventoryService,
        Squirrel3 $squirrel3, PetExperienceService $petExperienceService,
        HattierService $hattierService, EnchantmentRepository $enchantmentRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->squirrel3 = $squirrel3;
        $this->petExperienceService = $petExperienceService;
        $this->hattierService = $hattierService;
        $this->enchantmentRepository = $enchantmentRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills, GreenhousePlant $plant): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getDexterity()->getTotal();
        $skill = NumberFunctions::clamp($skill, 10, 15);

        $roll = $this->squirrel3->rngNextInt(1, $skill);

        $changes = new PetChanges($pet);

        $this->petExperienceService->gainAffection($pet, 1);

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

    public function maybeUnlockBeeAura(Pet $pet, PetActivityLog $activityLog, bool $beeNettingIsDeployed): bool
    {
        $forTheBees = $this->enchantmentRepository->findOneByName('for the Bees');

        if($this->hattierService->userHasUnlocked($pet->getOwner(), $forTheBees))
            return false;

        if($beeNettingIsDeployed)
        {
            $this->hattierService->unlockAuraDuringPetActivity(
                $pet,
                $activityLog,
                $forTheBees,
                'On the way back home, ' . ActivityHelpers::PetName($pet) . ' spotted a worker bee caught in the bee netting, and took it home in their hat!',
                'On the way back home, ' . ActivityHelpers::PetName($pet) . ' spotted a worker bee caught in the bee netting, and thought it\'d make a great addition to a hat (for some reason...)',
                ActivityHelpers::PetName($pet) . ' found a worker bee caught in your Greenhouse\'s bee netting...'
            );
        }
        else
        {
            $this->hattierService->unlockAuraDuringPetActivity(
                $pet,
                $activityLog,
                $forTheBees,
                'On the way back home, ' . ActivityHelpers::PetName($pet) . ' noticed that a worker bee had made a new home in their hat!',
                'On the way back home, ' . ActivityHelpers::PetName($pet) . ' noticed that a worker bee had followed them home!',
                ActivityHelpers::PetName($pet) . ' was followed home by a worker bee...'
            );
        }

        return true;
    }
}
