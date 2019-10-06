<?php

namespace App\Entity;

use App\Enum\LocationEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventoryRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="modified_on_idx", columns={"modified_on"}),
 *     @ORM\Index(name="sell_price_idx", columns={"sell_price"}),
 *     @ORM\Index(name="location_idx", columns={"location"})
 * })
 */
class Inventory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "myInventory", "marketItem", "greenhousePlant", "mySeeds"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="inventory")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "marketItem", "greenhousePlant", "mySeeds", "hollowEarth"})
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"myInventory"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"myInventory"})
     */
    private $modifiedOn;

    /**
     * @ORM\Column(type="json")
     * @Groups({"myInventory"})
     */
    private $comments = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"myInventory"})
     */
    private $createdBy;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="tool")
     * @Groups({"myInventory"})
     */
    private $pet;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"myInventory", "marketItem"})
     */
    private $sellPrice;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $sellListDate;

    /**
     * @ORM\Column(type="smallint")
     */
    private $location = LocationEnum::HOME;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
        $this->modifiedOn = new \DateTimeImmutable();
    }

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getModifiedOn(): \DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    public function setModifiedOn(): self
    {
        $this->modifiedOn = new \DateTimeImmutable();

        return $this;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(string $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        // set (or unset) the owning side of the relation if necessary
        $newTool = $pet === null ? null : $this;
        if ($newTool !== $pet->getTool()) {
            $pet->setTool($newTool);
        }

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

        $this->sellPrice = $sellPrice;

        return $this;
    }

    /**
     * @Groups({"marketItem"})
     */
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
        return \ceil($sellPrice * 1.02);
    }

    public function getLocation(): int
    {
        return $this->location;
    }

    public function setLocation(int $location): self
    {
        if(!LocationEnum::isAValue($location))
            throw new \InvalidArgumentException('$location is not a valid LocationEnum value.');

        $this->location = $location;

        return $this;
    }
}
