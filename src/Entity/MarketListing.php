<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'market_listing_unique', columns: ['item_id'])]
#[ORM\Entity]
class MarketListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups("marketItem")
     */
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $item;

    /**
     * @Groups("marketItem")
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $minimumSellPrice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getMinimumSellPrice(): ?int
    {
        return $this->minimumSellPrice;
    }

    public function setMinimumSellPrice(?int $minimumSellPrice): self
    {
        $this->minimumSellPrice = $minimumSellPrice;

        return $this;
    }
}
