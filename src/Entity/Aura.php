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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet"})
     */
    private $image;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myPet"})
     */
    private $size;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet"})
     */
    private $centerX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet"})
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

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getCenterX(): float
    {
        return $this->centerX;
    }

    public function setCenterX(float $xOffset): self
    {
        $this->xOffset = $xOffset;

        return $this;
    }

    public function getCenterY(): float
    {
        return $this->centerY;
    }

    public function setCenterY(float $yOffset): self
    {
        $this->yOffset = $yOffset;

        return $this;
    }
}
