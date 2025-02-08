<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Item;
use App\Entity\ItemGroup;

/**
 * Only one instance of an item may be required; no item must ever be repeated, or the logic will break
 */
class HouseSimRecipe
{
    /**
     * @var Item[]|ItemQuantity[]|Item[][]|ItemGroup[]
     */
    public $ingredients;

    /**
     * @param Item[]|ItemQuantity[]|Item[][]|ItemGroup[] $ingredients
     */
    public function __construct(array $ingredients)
    {
        $this->ingredients = $ingredients;
    }
}
