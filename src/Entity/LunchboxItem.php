<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LunchboxItemRepository")
 */
class LunchboxItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="lunchboxItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", inversedBy="lunchboxItem", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet"})
     */
    private $inventoryItem;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getInventoryItem(): ?Inventory
    {
        return $this->inventoryItem;
    }

    public function setInventoryItem(Inventory $inventoryItem): self
    {
        $this->inventoryItem = $inventoryItem;

        return $this;
    }
}
