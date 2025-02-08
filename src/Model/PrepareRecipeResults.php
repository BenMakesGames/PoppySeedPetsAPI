<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Inventory;

final class PrepareRecipeResults
{
    /** @var Inventory[] */
    public array $inventory;

    /** @var ItemQuantity[] */
    public array $quantities;

    public array $recipe;

    public int $location;
}
