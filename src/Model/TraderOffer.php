<?php
namespace App\Model;

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

    public function __construct(array $cost, array $yield, string $comment)
    {
        $this->id = self::GenerateID($cost, $yield);
        $this->cost = $cost;
        $this->yield = $yield;
        $this->comment = $comment;
    }

    private static function GenerateID(array $cost, array $yield): string
    {
        $costsAndYields = array_merge($cost, $yield);

        return sha1(implode('&', array_map(function(TraderOfferCostOrYield $coy) {
            return $coy->quantity . 'x' . ($coy->item ? $coy->item->getName() : $coy->type);
        }, $costsAndYields)));
    }
}
