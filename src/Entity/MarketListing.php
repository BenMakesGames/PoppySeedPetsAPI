<?php

namespace App\Entity;

use App\Repository\MarketListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MarketListingRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="market_listing_unique", columns={"item_id", "non_nullable_enchantment", "non_nullable_spice"})
 *     }
 * )
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
     * @Groups("marketItem")
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity=Enchantment::class)
     * @Groups("marketItem")
     */
    private $enchantment;

    /**
     * @ORM\Column(type="integer")
     */
    private $nonNullableEnchantment = -1;

    /**
     * @ORM\ManyToOne(targetEntity=Spice::class)
     * @Groups("marketItem")
     */
    private $spice;

    /**
     * @ORM\Column(type="integer")
     */
    private $nonNullableSpice = -1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("marketItem")
     */
    private $minimumSellPrice;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $fullItemName;

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
        $this->nonNullableEnchantment = $enchantment ? $enchantment->getId() : -1;

        return $this;
    }

    public function getSpice(): ?Spice
    {
        return $this->spice;
    }

    public function setSpice(?Spice $spice): self
    {
        $this->spice = $spice;
        $this->nonNullableSpice = $spice ? $spice->getId() : -1;

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

    public function getFullItemName(): ?string
    {
        return $this->fullItemName;
    }

    public function setFullItemName(string $fullItemName): self
    {
        $this->fullItemName = $fullItemName;

        return $this;
    }
}
