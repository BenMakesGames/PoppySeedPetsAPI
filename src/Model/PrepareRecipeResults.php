<?php
namespace App\Model;

use App\Entity\Inventory;
use App\Entity\Recipe;

class PrepareRecipeResults
{
    /** @var Inventory[] */
    public $inventory;

    /** @var ItemQuantity[] */
    public $quantities;

    /** @var Recipe */
    public $recipe;

    /** @var int */
    public $location;
}
