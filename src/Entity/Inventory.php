<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventoryRepository")
 */
class Inventory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "myInventory"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile"})
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
     */
    private $sellPrice;

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

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeImmutable $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getModifiedOn(): ?\DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    public function setModifiedOn(\DateTimeImmutable $modifiedOn): self
    {
        $this->modifiedOn = $modifiedOn;

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
        $this->sellPrice = $sellPrice;

        return $this;
    }
}
