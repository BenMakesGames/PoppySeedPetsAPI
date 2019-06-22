<?php

namespace App\Entity;

use App\Model\ItemFood;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 */
class Item
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"encyclopedia"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"myPets", "myInventory", "publicProfile", "encyclopedia"})
     */
    private $name;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $food = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"encyclopedia"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPets", "myInventory", "publicProfile", "encyclopedia"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=40, name="color_a_range")
     */
    private $colorARange = '0-0,100-100,100-100';

    /**
     * @ORM\Column(type="string", length=40, name="color_b_range")
     */
    private $colorBRange = '0-0,100-100,100-100';

    /**
     * @Groups({"myInventory", "encyclopedia"})
     */
    public function isEdible(): bool
    {
        return $this->food !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFood(): ?ItemFood
    {
        return $this->food;
    }

    public function setFood(?ItemFood $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getColorARange(): ?string
    {
        return $this->colorARange;
    }

    public function setColorARange(string $colorARange): self
    {
        $this->colorARange = $colorARange;

        return $this;
    }

    public function getColorBRange(): ?string
    {
        return $this->colorBRange;
    }

    public function setColorBRange(string $colorBRange): self
    {
        $this->colorBRange = $colorBRange;

        return $this;
    }
}
