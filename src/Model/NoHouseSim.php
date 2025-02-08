<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Service\IRandom;

class NoHouseSim implements IHouseSim
{
    public function getInventoryCount(): int
    {
        NoHouseSim::throwException();
    }

    public function hasInventory(HouseSimRecipe $recipe): bool
    {
        NoHouseSim::throwException();
    }

    public function loseItem(Item|string $item, int $quantity = 1)
    {
        NoHouseSim::throwException();
    }

    /**
     * @param Item[]|string[] $items
     */
    public function loseOneOf(IRandom $rng, array $items): string
    {
        NoHouseSim::throwException();
    }

    public function addInventory(?Inventory $i): bool
    {
        // don't throw an exception here, since inventory can legit be added outside of house hours
        return false;
    }

    public function getInventoryToRemove(): array
    {
        NoHouseSim::throwException();
    }

    public function getInventoryToPersist(): array
    {
        NoHouseSim::throwException();
    }

    private static function throwException()
    {
        throw new \Exception('Ben did a bad programming thing. He\'s been emailed...');
    }
}
