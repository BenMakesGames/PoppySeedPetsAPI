<?php
namespace App\Model;

use App\Entity\Inventory;

class NoHouseSim implements IHouseSim
{
    public function getInventoryCount(): int
    {
        return 0;
    }

    public function getInventory(HouseSimRecipe $recipe): ?array
    {
        return null;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function removeInventory(array $toRemove)
    {
    }

    public function addInventory(array $toAdd)
    {
    }

    public function addSingleInventory(?Inventory $i)
    {
    }
}
