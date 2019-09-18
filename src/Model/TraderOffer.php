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

    public function __construct(string $id, array $cost, array $yield, string $comment)
    {
        $this->id = $id;
        $this->cost = $cost;
        $this->yield = $yield;
        $this->comment = $comment;
    }
}