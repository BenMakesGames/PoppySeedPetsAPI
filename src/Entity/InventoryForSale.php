<?php

namespace App\Entity;

use App\Repository\InventoryForSaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InventoryForSaleRepository::class)]
#[ORM\Index(name: 'sell_price_idx', columns: ['sell_price'])]
#[ORM\Index(name: 'sell_list_date_idx', columns: ['sell_list_date'])]
class InventoryForSale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'forSale')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inventory $inventory = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(["myInventory", "fireplaceFuel", "myGreenhouse", "myPet", 'houseSitterPet', "dragonTreasure", "myHollowEarthTiles"])]
    private $sellPrice;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $sellListDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(Inventory $inventory): static
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getSellPrice(): ?int
    {
        return $this->sellPrice;
    }

    public function setSellPrice(?int $sellPrice): self
    {
        if($sellPrice === null)
            $this->sellListDate = null;
        else if($sellPrice !== $this->sellPrice)
            $this->sellListDate = new \DateTimeImmutable();

        if($sellPrice < 1)
            throw new \InvalidArgumentException("sellPrice cannot be less than 1.");

        $this->sellPrice = $sellPrice;

        return $this;
    }

    public function getBuyPrice(): ?int
    {
        if($this->sellPrice === null || $this->sellPrice <= 0) return null;

        return self::calculateBuyPrice($this->sellPrice);
    }

    public function getSellListDate(): ?\DateTimeImmutable
    {
        return $this->sellListDate;
    }

    public static function calculateBuyPrice(int $sellPrice): int
    {
        return ceil($sellPrice * 1.02);
    }
}
