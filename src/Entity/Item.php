<?php

namespace App\Entity;

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
     * @Groups({"itemEncyclopedia", "myPet", "itemAdmin", "marketItem"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "itemAdmin", "museum", "marketItem"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"myInventory", "itemEncyclopedia", "itemAdmin"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "itemAdmin", "museum", "marketItem"})
     */
    private $image;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"myInventory", "itemEncyclopedia", "itemAdmin"})
     */
    private $useActions = [];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemTool")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia"})
     */
    private $tool;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemFood", cascade={"persist", "remove"})
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $food;

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

    public function getUseActions(): ?array
    {
        return $this->useActions;
    }

    public function setUseActions(?array $useActions): self
    {
        $this->useActions = $useActions;

        return $this;
    }

    public function hasUseAction(string $action): bool
    {
        if($this->useActions === null) return false;

        foreach($this->useActions as $useAction)
        {
            if($useAction[1] === $action)
                return true;
        }

        return false;
    }

    public function getTool(): ?ItemTool
    {
        return $this->tool;
    }

    public function setTool(?ItemTool $tool): self
    {
        $this->tool = $tool;

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
}
