<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Item;
use Symfony\Component\Serializer\Attribute\Groups;

class ItemQuantity
{
    #[Groups(['myInventory', 'knownRecipe'])]
    public Item $item;

    #[Groups(['myInventory', 'knownRecipe'])]
    public int $quantity;

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
