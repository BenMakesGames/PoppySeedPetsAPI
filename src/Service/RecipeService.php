<?php
namespace App\Service;

use App\Entity\Recipe;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;

class RecipeService
{
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * @return ItemQuantity[]
     */
    public function deserializeItemList(string $list)
    {
        if($list === '') return [];

        $quantities = [];

        $items = explode(',', $list);
        foreach($items as $item)
        {
            list($itemId, $quantity) = explode(':', $item);
            $itemQuantity = new ItemQuantity();

            $itemQuantity->item = $this->itemRepository->find($itemId);
            $itemQuantity->quantity = $quantity;

            $quantities[] = $itemQuantity;
        }

        return $quantities;
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public function serializeItemList($quantities): string
    {
        if(count($quantities) === 0) return '';

        usort($quantities, function(ItemQuantity $a, ItemQuantity $b) {
            return $a->item->getId() <=> $b->item->getId();
        });

        $items = [];

        foreach($quantities as $itemQuantity)
        {
            $items[] = $itemQuantity->item->getId() . ':' . $itemQuantity->quantity;
        }

        return implode(',', $items);
    }
}