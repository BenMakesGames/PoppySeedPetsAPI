<?php
namespace App\Model;

use App\Entity\Inventory;

class PrepareRecipeResults
{
    /** @var Inventory[] */
    public $inventory;

    /** @var ItemQuantity[] */
    public $quantities;

    /** @var array */
    public $recipe;

    /** @var int */
    public $location;
}
