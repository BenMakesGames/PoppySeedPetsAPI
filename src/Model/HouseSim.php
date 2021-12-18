<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Functions\ArrayFunctions;
use App\Service\IRandom;

class HouseSim implements IHouseSim
{
    /** @var Inventory[] */ private array $inventory;
    /** @var Inventory[] */ private array $inventoryToRemoveFromDatabase = [];

    private array $itemQuantitiesByItemId = [];

    private IRandom $rng;

    /**
     * @param Inventory[] $inventory
     */
    public function __construct(IRandom $rng, array $inventory)
    {
        $this->rng = $rng;
        $this->setInventory($inventory);
    }

    /**
     * @param Inventory[] $inventory
     */
    private function setInventory(array $inventory)
    {
        $this->inventory = $inventory;

        $this->itemQuantitiesByItemId = [];

        $this->addInventoryToItemQuantities($this->inventory);
    }

    /**
     * @param Inventory[] $inventory
     */
    private function addInventoryToItemQuantities(array $inventory)
    {
        foreach($inventory as $i)
        {
            $itemId = $i->getItem()->getId();

            if(array_key_exists($itemId, $this->itemQuantitiesByItemId))
                $this->itemQuantitiesByItemId[$itemId]++;
            else
                $this->itemQuantitiesByItemId[$itemId] = 1;
        }
    }

    public function getInventoryCount(): int
    {
        return count($this->inventory);
    }

    /**
     * @param HouseSimRecipe $recipe
     */
    public function hasInventory(HouseSimRecipe $recipe): bool
    {
        foreach($recipe->ingredients as $ingredient)
        {
            if($ingredient instanceof Item)
            {
                $itemId = $ingredient->getId();

                if(!array_key_exists($itemId, $this->itemQuantitiesByItemId))
                    return false;
            }
            else if($ingredient instanceof ItemQuantity)
            {
                $itemId = $ingredient->item->getId();
                $quantity = $ingredient->quantity;

                if(!array_key_exists($itemId, $this->itemQuantitiesByItemId) || $this->itemQuantitiesByItemId[$itemId] < $quantity)
                    return false;
            }
            else
            {
                if($ingredient instanceof ItemGroup)
                    $possibleItems = $ingredient->getItems()->toArray();
                else
                    $possibleItems = $ingredient;

                if(!ArrayFunctions::any(
                    $possibleItems,
                    fn(Item $i) => array_key_exists($i->getId(), $this->itemQuantitiesByItemId)
                ))
                    return false;
            }
        }

        return true;
    }

    /**
     * @param Item[]|string[] $items
     */
    public function loseOneOf(array $items): string
    {
        $items = array_map(
            fn($item) => is_string($item) ? $item : $item->getName(),
            $items
        );

        $this->rng->rngNextShuffle($items);

        /** @var Inventory $itemToRemove */
        $itemToRemove = ArrayFunctions::find_one(
            $this->inventory,
            fn(Inventory $i) => in_array($i->getItem()->getName(), $items)
        );

        if(!$itemToRemove)
            throw new \Exception('Cannot use ' . ArrayFunctions::list_nice($items, ', ', ', or ') . '; none exist in your house!');

        $itemId = $itemToRemove->getItem()->getId();

        if($this->itemQuantitiesByItemId[$itemId] === 1)
            unset($this->itemQuantitiesByItemId[$itemId]);
        else
            $this->itemQuantitiesByItemId[$itemId]--;

        if($itemToRemove->getId())
            $this->inventoryToRemoveFromDatabase[] = $itemToRemove;

        $this->inventory = array_filter(
            $this->inventory,
            fn(Inventory $i) => $i !== $itemToRemove
        );

        return $itemToRemove->getItem()->getName();
    }

    /**
     * @param Item|string $item
     */
    public function loseItem($item, $quantity = 1)
    {
        if(!is_string($item))
            $item = $item->getName();

        /** @var Inventory[] $inventoryToRemoveFromHouseSim */
        $inventoryToRemoveFromHouseSim = ArrayFunctions::find_n(
            $this->inventory,
            fn(Inventory $i) => $i->getItem()->getName() === $item,
            $quantity
        );

        if(count($inventoryToRemoveFromHouseSim) < $quantity)
            throw new \Exception('Cannot use ' . $quantity . 'x ' . $item . '; not enough exist in your house!');

        $itemId = $inventoryToRemoveFromHouseSim[0]->getItem()->getId();

        if($this->itemQuantitiesByItemId[$itemId] > $quantity)
            $this->itemQuantitiesByItemId[$itemId]--;
        else
            unset($this->itemQuantitiesByItemId[$itemId]);

        foreach($inventoryToRemoveFromHouseSim as $itemToRemove)
        {
            if($itemToRemove->getId())
                $this->inventoryToRemoveFromDatabase[] = $itemToRemove;
        }

        $this->inventory = array_filter(
            $this->inventory,
            fn(Inventory $i) => !ArrayFunctions::find_one($inventoryToRemoveFromHouseSim, fn(Inventory $j) => $i === $j)
        );
    }

    public function addInventory(?Inventory $i): bool
    {
        if($i === null)
            return true;

        $this->inventory[] = $i;

        $itemId = $i->getItem()->getId();

        if(array_key_exists($itemId, $this->itemQuantitiesByItemId))
            $this->itemQuantitiesByItemId[$itemId]++;
        else
            $this->itemQuantitiesByItemId[$itemId] = 1;

        return true;
    }

    public function getInventoryToRemove(): array
    {
        return $this->inventoryToRemoveFromDatabase;
    }

    public function getInventoryToPersist(): array
    {
        return array_filter(
            $this->inventory,
            fn($i) => $i->getId() === null
        );
    }
}
