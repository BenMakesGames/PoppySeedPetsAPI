<?php

namespace App\Entity;

use App\Repository\MarketListingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MarketListingRepository::class)
 */
class MarketListing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="marketListings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity=Enchantment::class)
     */
    private $enchantment;

    /**
     * @ORM\ManyToOne(targetEntity=Spice::class)
     */
    private $spice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
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

    public function getEnchantment(): ?Enchantment
    {
        return $this->enchantment;
    }

    public function setEnchantment(?Enchantment $enchantment): self
    {
        $this->enchantment = $enchantment;

        return $this;
    }

    public function getSpice(): ?Spice
    {
        return $this->spice;
    }

    public function setSpice(?Spice $spice): self
    {
        $this->spice = $spice;

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
