<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Service\IRandom;

interface IHouseSim
{
    public function getInventoryCount(): int;
    public function hasInventory(HouseSimRecipe $recipe): bool;

    public function loseItem(Item|string $item, int $quantity = 1);

    /**
     * @param Item[]|string[] $items
     */
    public function loseOneOf(IRandom $rng, array $items): string;

    public function addInventory(?Inventory $i): bool;

    /**
     * @return Inventory[]
     */
    public function getInventoryToRemove(): array;

    /**
     * @return Inventory[]
     */
    public function getInventoryToPersist(): array;
}