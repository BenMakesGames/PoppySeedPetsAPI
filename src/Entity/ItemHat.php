<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemHatRepository")
 */
class ItemHat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent"})
     */
    private $headX = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent"})
     */
    private $headY = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent"})
     */
    private $headAngle = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent"})
     */
    private $headScale = 0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent"})
     */
    private $headAngleFixed = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Item", mappedBy="hat")
     */
    private $item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeadX(): ?float
    {
        return $this->headX;
    }

    public function setHeadX(float $headX): self
    {
        $this->headX = $headX;

        return $this;
    }

    public function getHeadY(): ?float
    {
        return $this->headY;
    }

    public function setHeadY(float $headY): self
    {
        $this->headY = $headY;

        return $this;
    }

    public function getHeadAngle(): ?float
    {
        return $this->headAngle;
    }

    public function setHeadAngle(float $headAngle): self
    {
        $this->headAngle = $headAngle;

        return $this;
    }

    public function getHeadScale(): ?float
    {
        return $this->headScale;
    }

    public function setHeadScale(float $headScale): self
    {
        $this->headScale = $headScale;

        return $this;
    }

    public function getHeadAngleFixed(): ?bool
    {
        return $this->headAngleFixed;
    }

    public function setHeadAngleFixed(bool $headAngleFixed): self
    {
        $this->headAngleFixed = $headAngleFixed;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        // set (or unset) the owning side of the relation if necessary
        $newHat = $item === null ? null : $this;
        if ($newHat !== $item->getHat()) {
            $item->setHat($newHat);
        }

        return $this;
    }
}
