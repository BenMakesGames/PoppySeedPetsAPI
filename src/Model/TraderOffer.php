<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use Symfony\Component\Serializer\Annotation\Groups;

class TraderOffer
{
    /**
     * @var string
     * @Groups({"traderOffer"})
     */
    public $id;

    /**
     * @var TraderOfferCostOrYield[]
     * @Groups({"traderOffer"})
     */
    public $cost;

    /**
     * @var TraderOfferCostOrYield[]
     * @Groups({"traderOffer"})
     */
    public $yield;

    /**
     * @var string
     * @Groups({"traderOffer"})
     */
    public $comment;

    /**
     * @var int
     * @Groups({"traderOffer"})
     */
    public $canMakeExchange;

    /**
     * @var bool
     * @Groups({"traderOffer"})
     */
    public $lockedToAccount;

    public static function createTradeOffer(
        array $cost, array $yield, string $comment, User $user, array $inventoryQuantitiesByNameAtLocation, bool $lockedToAccount = false
    ): TraderOffer
    {
        $trade = new TraderOffer();

        $trade->id = self::generateID($cost, $yield);
        $trade->cost = $cost;
        $trade->yield = $yield;
        $trade->comment = $comment;
        $trade->canMakeExchange = TraderOffer::getMaxExchanges($cost, $user, $inventoryQuantitiesByNameAtLocation);
        $trade->lockedToAccount = $lockedToAccount;

        return $trade;
    }

    /**
     * @param TraderOfferCostOrYield[] $costs
     * @param ItemQuantity[] $houseInventoryQuantitiesByName
     */
    private static function getMaxExchanges(array $costs, User $user, array $houseInventoryQuantitiesByName): int
    {
        $maxQuantity = 9999;

        foreach($costs as $cost)
        {
            if($cost->type === CostOrYieldTypeEnum::ITEM)
            {
                if(!array_key_exists($cost->item->getName(), $houseInventoryQuantitiesByName))
                    return 0;

                $maxQuantity = min($maxQuantity, (int)($houseInventoryQuantitiesByName[$cost->item->getName()]->quantity /  $cost->quantity));
            }
            else if($cost->type === CostOrYieldTypeEnum::MONEY)
                $maxQuantity = min($maxQuantity, (int)($user->getMoneys() / $cost->quantity));
            else if($cost->type === CostOrYieldTypeEnum::RECYCLING_POINTS)
                $maxQuantity = min($maxQuantity, (int)($user->getRecyclePoints() / $cost->quantity));

            if($maxQuantity == 0)
                return 0;
        }

        return $maxQuantity;
    }

    private static function generateID(array $cost, array $yield): string
    {
        $costsAndYields = array_merge($cost, $yield);

        return sha1(implode('&', array_map(function(TraderOfferCostOrYield $coy) {
            return $coy->quantity . 'x' . ($coy->item ? $coy->item->getName() : $coy->type);
        }, $costsAndYields)));
    }
}
