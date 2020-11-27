<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;

class EvericeMeltingService
{
    private $inventoryService;
    private $itemRepository;
    private $spiceRepository;
    private $responseService;

    public function __construct(
        InventoryService $inventoryService, ItemRepository $itemRepository, SpiceRepository $spiceRepository,
        ResponseService $responseService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
        $this->spiceRepository = $spiceRepository;
        $this->responseService = $responseService;
    }

    public function doMeltEverice(Pet $pet, string $description): PetActivityLog
    {
        $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);

        if(mt_rand(1, 2) === 1)
        {
            $unexpectedLoot = $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
                'Fish', 'Feathers', 'Sand Dollar', 'Sweet Beet', 'Beans'
            ]));

            $spice = $this->spiceRepository->findOneByName('Freezer-burned');

            $activityLog = $this->responseService->createActivityLog($pet, $description . ' However, they found ' . $unexpectedLoot->getNameWithArticle() . ' had been trapped in the block of Everice, and thawed it out!', '');

            $this->inventoryService->petCollectsEnhancedItem($unexpectedLoot, null, $spice, $pet, $pet->getName() . ' found this perfectly preserved inside a block of Everice!', $activityLog);

            return $activityLog;
        }
        else
            return $this->responseService->createActivityLog($pet, $description, '');
    }
}
