<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Aura
{
    #[Groups(['myPet', 'myAura'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    /**
     * @Groups({"myPet", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $image;

    /**
     * @Groups({"myPet", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    #[ORM\Column(type: 'float')]
    private $size;

    /**
     * @Groups({"myPet", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    #[ORM\Column(type: 'float')]
    private $centerX;

    /**
     * @Groups({"myPet", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    #[ORM\Column(type: 'float')]
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
