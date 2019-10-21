<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $headX;

    /**
     * @ORM\Column(type="float")
     */
    private $headY;

    /**
     * @ORM\Column(type="float")
     */
    private $headAngle;

    /**
     * @ORM\Column(type="float")
     */
    private $headScale;

    /**
     * @ORM\Column(type="boolean")
     */
    private $headAngleFixed;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Item", mappedBy="hat", cascade={"persist", "remove"})
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
