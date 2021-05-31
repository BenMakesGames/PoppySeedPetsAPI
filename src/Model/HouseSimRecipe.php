<?php
namespace App\Model;

use App\Entity\Item;
use App\Entity\ItemGroup;

/**
 * Only one instance of an item may be required; no item must ever be repeated, or the logic will break
 */
class HouseSimRecipe
{
    /**
     * @var <Item|Item[]|ItemGroup>[]
     */
    public $ingredients;

    /**
     * @param <Item|Item[]|ItemGroup>[] $ingredients
     */
    public function __construct($ingredients)
    {
        $this->ingredients = $ingredients;
    }
}