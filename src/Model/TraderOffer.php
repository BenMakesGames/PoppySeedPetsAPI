<?php
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
     * @var bool
     * @Groups({"traderOffer"})
     */
    public $canMakeExchange;

    /**
     * @var bool
     * @Groups({"traderOffer"})
     */
    public $lockedToAccount;

    public static function createTradeOffer(
        array $cost, array $yield, string $comment, User $user, array $houseInventoryQuantitiesByName, bool $lockedToAccount = false
    ): TraderOffer
    {
        $trade = new TraderOffer();

        $trade->id = self::generateID($cost, $yield);
        $trade->cost = $cost;
        $trade->yield = $yield;
        $trade->comment = $comment;
        $trade->canMakeExchange = TraderOffer::userCanMakeExchange($cost, $user, $houseInventoryQuantitiesByName);
        $trade->lockedToAccount = $lockedToAccount;

        return $trade;
    }

    /**
     * @param TraderOfferCostOrYield[] $costs
     * @param User $user
     * @param ItemQuantity[] $houseInventoryQuantitiesByName
     * @return bool
     */
    private static function userCanMakeExchange(array $costs, User $user, array $houseInventoryQuantitiesByName): bool
    {
        foreach($costs as $cost)
        {
            if($cost->type === CostOrYieldTypeEnum::ITEM)
            {
                if(!array_key_exists($cost->item->getName(), $houseInventoryQuantitiesByName))
                    return false;

                if($houseInventoryQuantitiesByName[$cost->item->getName()]->quantity < $cost->quantity)
                    return false;
            }
            else if($cost->type === CostOrYieldTypeEnum::MONEY)
            {
                if($user->getMoneys() < $cost->quantity)
                    return false;
            }
            else if($cost->type === CostOrYieldTypeEnum::RECYCLING_POINTS)
            {
                if($user->getRecyclePoints() < $cost->quantity)
                    return false;
            }
        }

        return true;
    }

    private static function generateID(array $cost, array $yield): string
    {
        $costsAndYields = array_merge($cost, $yield);

        return sha1(implode('&', array_map(function(TraderOfferCostOrYield $coy) {
            return $coy->quantity . 'x' . ($coy->item ? $coy->item->getName() : $coy->type);
        }, $costsAndYields)));
    }
}
