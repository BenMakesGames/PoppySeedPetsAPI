<?php
namespace App\Model;

use App\Entity\Item;
use Symfony\Component\Serializer\Annotation\Groups;

class ItemQuantity
{
    /**
     * @var Item
     * @Groups({"myInventory", "knownRecipe"})
     */
    public $item;

    /**
     * @var int
     * @Groups({"myInventory", "knownRecipe"})
     */
    public $quantity;

    /**
     * @param ItemQuantity[] $quantities
     * @return ItemQuantity[]
     */
    public static function divide(array $quantities, int $divisor): array
    {
        $dividedQuantities = [];

        foreach($quantities as $quantity)
        {
            if($quantity->quantity % $divisor !== 0)
                throw new \InvalidArgumentException('$quantities cannot be evenly divided by $divisor (' . $divisor . ')');

            $q = new ItemQuantity();
            $q->item = $quantity->item;
            $q->quantity = $quantity->quantity / $divisor;

            $dividedQuantities[] = $q;
        }

        return $dividedQuantities;
    }
}
