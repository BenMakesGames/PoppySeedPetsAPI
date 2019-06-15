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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"myPets", "myInventory"})
     */
    private $name;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $food = null;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myInventory"})
     */
    private $size = 100;

    /**
     * @Groups({"myInventory"})
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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
