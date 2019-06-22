<?php
namespace App\Model;

use App\Entity\Item;
use Symfony\Component\Serializer\Annotation\Groups;

class ItemQuantity
{
    /**
     * @var Item
     * @Groups({"myInventory"})
     */
    public $item;

    /**
     * @var int
     * @Groups({"myInventory"})
     */
    public $quantity;
}