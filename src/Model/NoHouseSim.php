<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Item;

class NoHouseSim implements IHouseSim
{
    public function getInventoryCount(): int
    {
        $this->throwException();
    }

    public function hasInventory(HouseSimRecipe $recipe): bool
    {
        $this->throwException();
    }

    /**
     * @param Item|string $item
     */
    public function loseItem($item, $quantity = 1)
    {
        $this->throwException();
    }

    /**
     * @param Item[]|string[] $items
     */
    public function loseOneOf(array $items): string
    {
        $this->throwException();
    }

    public function addInventory(?Inventory $i): bool
    {
        // don't thrown an exception here, since inventory can legit be added outside of house hours
        return false;
    }

    public function getInventoryToRemove(): array
    {
        $this->throwException();
    }

    public function getInventoryToPersist(): array
    {
        $this->throwException();
    }

    private function throwException()
    {
        throw new \Exception('Ben did a bad programming thing. He\'s been emailed...');
    }
}
