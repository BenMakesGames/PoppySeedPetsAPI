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
     * @Groups({"myPet", "myInventory", "userPublicProfile"})
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
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
     * @ORM\Column(type="string", length=6)
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $colorB;

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

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }
}
