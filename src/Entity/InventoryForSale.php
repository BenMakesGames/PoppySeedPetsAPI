<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
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
    private Inventory $inventory;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(["myInventory", "fireplaceFuel", "myGreenhouse", "myPet", "dragonTreasure", "myHollowEarthTiles"])]
    private ?int $sellPrice;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sellListDate = null;

    public function __construct(Inventory $inventory, int $sellPrice)
    {
        $this->inventory = $inventory;
        $this->sellPrice = $sellPrice;
    }

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
        return (int)ceil($sellPrice * 1.02);
    }
}
