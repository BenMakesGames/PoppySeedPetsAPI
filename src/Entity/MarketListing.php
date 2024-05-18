<?php

namespace App\Entity;

use App\Repository\MarketListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'market_listing_unique', columns: ['item_id', 'non_nullable_enchantment', 'non_nullable_spice'])]
#[ORM\Entity(repositoryClass: MarketListingRepository::class)]
class MarketListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups("marketItem")
     */
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'marketListings')]
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
