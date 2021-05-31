<?php
namespace App\Model;

use App\Entity\Inventory;

interface IHouseSim
{
    public function getInventoryCount(): int;
    public function getInventory(HouseSimRecipe $recipe): ?array;

    public function removeInventory(array $toRemove);
    public function addInventory(array $toAdd);
    public function addSingleInventory(?Inventory $i);
}