<?php

namespace App\Entity;

use App\Repository\AuraRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AuraRepository::class)
 */
class Aura
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "myAura"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "myAura"})
     */
    private $image;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "myAura"})
     */
    private $size;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "myAura"})
     */
    private $centerX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "myAura"})
     */
    private $centerY;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSize(): float
    {
        return $this->size;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getCenterX(): float
    {
        return $this->centerX;
    }

    public function setCenterX(float $centerX): self
    {
        $this->centerX = $centerX;

        return $this;
    }

    public function getCenterY(): float
    {
        return $this->centerY;
    }

    public function setCenterY(float $centerY): self
    {
        $this->centerY = $centerY;

        return $this;
    }
}
