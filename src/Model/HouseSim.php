<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Service\IRandom;

class HouseSim implements IHouseSim
{
    /** @var Inventory[] */ private array $inventory;
    private array $inventoryByItemId;

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

        $this->inventoryByItemId = [];

        $this->addInventoryByItemId($this->inventory);
    }

    /**
     * @param Inventory[] $inventory
     */
    private function addInventoryByItemId(array $inventory)
    {
        foreach($inventory as $i)
        {
            $itemId = $i->getItem()->getId();

            if(array_key_exists($itemId, $this->inventoryByItemId))
                $this->inventoryByItemId[$itemId][] = $i;
            else
                $this->inventoryByItemId[$itemId] = [ $i ];
        }
    }

    public function getInventoryCount(): int
    {
        return count($this->inventory);
    }

    /**
     * @param HouseSimRecipe $recipe
     * @param IRandom $rng
     * @return array|null
     */
    public function getInventory(HouseSimRecipe $recipe): ?array
    {
        $items = [];

        foreach($recipe->ingredients as $ingredient)
        {
            if($ingredient instanceof Item)
            {
                $itemId = $ingredient->getId();

                if(array_key_exists($itemId, $this->inventoryByItemId))
                    $items[] = $this->rng->rngNextFromArray($this->inventoryByItemId[$itemId]);
                else
                    return null;
            }
            else if($ingredient instanceof ItemQuantity)
            {
                $itemId = $ingredient->item->getId();
                $quantity = $ingredient->quantity;

                if(array_key_exists($itemId, $this->inventoryByItemId) && count($this->inventoryByItemId[$itemId]) >= $quantity)
                    $items = $this->rng->rngNextSubsetFromArray($this->inventoryByItemId[$itemId], $quantity);
                else
                    return null;
            }
            else
            {
                if($ingredient instanceof ItemGroup)
                    $possibleItems = $ingredient->getItems()->toArray();
                else
                    $possibleItems = $ingredient;

                $this->rng->rngNextShuffle($possibleItems);

                $inventory = $this->findFirstListOf($possibleItems);

                if($inventory === null)
                    return null;
                else
                    $items[] = $this->rng->rngNextFromArray($inventory);
            }
        }

        return $items;
    }

    /**
     * @param Item[] $items
     * @return Inventory[]|null
     */
    private function findFirstListOf(array $items): ?Item
    {
        foreach($items as $i)
        {
            $itemId = $i->getId();

            if(array_key_exists($itemId, $this->inventoryByItemId))
                return $this->inventoryByItemId[$itemId];
        }

        return null;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function removeInventory(array $toRemove)
    {
        $itemIdsToRemove = array_map(fn(Inventory $i) => $i->getId(), $toRemove);

        $newInventory = array_filter(
            $this->inventory,
            fn(Inventory $i) => !in_array($i->getId(), $itemIdsToRemove)
        );

        $this->setInventory($newInventory);
    }

    public function addInventory(array $toAdd)
    {
        $this->inventory = array_merge($this->inventory, $toAdd);

        $this->addInventoryByItemId($toAdd);
    }

    public function addSingleInventory(?Inventory $i)
    {
        if($i === null)
            return;

        $this->inventory[] = $i;

        $itemId = $i->getItem()->getId();

        if(array_key_exists($itemId, $this->inventoryByItemId))
            $this->inventoryByItemId[$itemId][] = $i;
        else
            $this->inventoryByItemId[$itemId] = [ $i ];
    }
}
