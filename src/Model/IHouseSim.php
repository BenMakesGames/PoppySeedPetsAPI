<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Service\IRandom;

interface IHouseSim
{
    public function getItemCount(): int;
    public function getItems(HouseSimRecipe $recipe, IRandom $rng): ?array;

    public function removeInventory(array $toRemove);
    public function addInventory(array $toAdd);
    public function addSingleInventory(?Inventory $i);
}