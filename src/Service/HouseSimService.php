<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Model\HouseSim;
use App\Model\HouseSimRecipe;
use App\Model\IHouseSim;
use App\Model\ItemQuantity;
use App\Model\NoHouseSim;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;

class HouseSimService
{
    // services
    private InventoryRepository $inventoryRepository;
    private ItemRepository $itemRepository;
    private IRandom $rng;

    // data
    private IHouseSim $houseState;

    public function __construct(InventoryRepository $inventoryRepository, ItemRepository $itemRepository, Squirrel3 $rng)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->rng = $rng;
        $this->itemRepository = $itemRepository;

        $this->houseState = new NoHouseSim();
    }

    public function begin(User $user)
    {
        $inventory = $this->inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $this->houseState = new HouseSim($this->rng, $inventory);
    }

    public function end()
    {
        $this->houseState = new NoHouseSim();
    }

    public function getState()
    {
        return $this->houseState;
    }

    public function hasInventory(string $itemName, int $quantity = 1): bool
    {
        if($quantity === 1)
        {
            $ingredient = $this->itemRepository->findOneByName($itemName);
        }
        else
        {
            $ingredient = new ItemQuantity();
            $ingredient->item = $this->itemRepository->findOneByName($itemName);
            $ingredient->quantity = $quantity;
        }

        $items = $this->getState()->getInventory(new HouseSimRecipe([ $ingredient ]));

        return $items !== null;
    }

}