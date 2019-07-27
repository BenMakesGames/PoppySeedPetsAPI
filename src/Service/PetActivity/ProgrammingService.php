<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\RefiningService;
use App\Service\PetActivity\Crafting\ScrollMakingService;
use App\Service\PetService;
use App\Service\ResponseService;

class ProgrammingService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $itemRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        ItemRepository $itemRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->itemRepository = $itemRepository;
    }

    public function getCraftingPossibilities(Pet $pet): array
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), 'name');

        $possibilities = [];

        if(array_key_exists('Pointer', $quantities))
        {
            $possibilities[] = [ $this, 'createStringFromPointer' ];
        }

        return $possibilities;
    }

    public function adventure(Pet $pet, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        $method = $possibilities[\mt_rand(0, count($possibilities) - 1)];

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = $method($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    private function createStringFromPointer(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'computer' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a String from a Pointer, but encountered a null exception :(');
        }
        else if($roll >= 10)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' dereferenced this from a Pointer.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'computer' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' dereferenced a String from a Pointer.');
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'computer' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a Pointer, but couldn\'t figure out all the syntax errors.');
        }
    }
}