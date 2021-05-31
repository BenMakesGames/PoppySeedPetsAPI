<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Service\IRandom;

class NoHouseSim implements IHouseSim
{
    public function getItemCount(): int
    {
        return 0;
    }

    public function getItems(HouseSimRecipe $recipe, IRandom $rng): ?array
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
