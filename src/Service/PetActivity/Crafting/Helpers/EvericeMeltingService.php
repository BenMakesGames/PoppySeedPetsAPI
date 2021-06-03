<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class EvericeMeltingService
{
    private $inventoryService;
    private $itemRepository;
    private $spiceRepository;
    private $responseService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        InventoryService $inventoryService, ItemRepository $itemRepository, SpiceRepository $spiceRepository,
        ResponseService $responseService, Squirrel3 $squirrel3, HouseSimService $houseSimService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
        $this->spiceRepository = $spiceRepository;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    public function doMeltEverice(Pet $pet, string $description): PetActivityLog
    {
        $this->houseSimService->getState()->loseItem('Everice', 1);

        if($this->squirrel3->rngNextBool())
        {
            $unexpectedLoot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
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
