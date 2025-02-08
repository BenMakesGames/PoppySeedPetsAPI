<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Item;
use App\Enum\CostOrYieldTypeEnum;
use Symfony\Component\Serializer\Annotation\Groups;

class TraderOfferCostOrYield
{
    /**
     * @var string
     * @Groups({"traderOffer"})
     */
    public $type;

    /**
     * @var Item|null
     * @Groups({"traderOffer"})
     */
    public $item;

    /**
     * @var int
     * @Groups({"traderOffer"})
     */
    public $quantity;

    public static function createItem(Item $item, int $quantity)
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::ITEM;
        $costOrYield->item = $item;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }

    public static function createMoney(int $quantity)
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::MONEY;
        $costOrYield->item = null;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }

    public static function createRecyclingPoints(int $quantity)
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::RECYCLING_POINTS;
        $costOrYield->item = null;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }
}
