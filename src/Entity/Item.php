<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="fertilizer_idx", columns={"fertilizer"})
 * })
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
     * @ORM\Column(type="string", length=45, unique=true)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "itemAdmin", "museum", "marketItem", "knownRecipe", "mySeeds", "greenhousePlant"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"myInventory", "itemEncyclopedia", "itemAdmin"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "itemAdmin", "museum", "marketItem", "knownRecipe", "mySeeds", "greenhousePlant"})
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

    /**
     * @ORM\Column(type="integer")
     */
    private $earth = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $water = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fire = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $wind = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $spirit = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fertilizer = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $nonTransferable = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemPlant", inversedBy="item", cascade={"persist", "remove"})
     */
    private $plant;

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

    public function getEarth(): ?int
    {
        return $this->earth;
    }

    public function setEarth(int $earth): self
    {
        $this->earth = $earth;

        return $this;
    }

    public function getWater(): ?int
    {
        return $this->water;
    }

    public function setWater(int $water): self
    {
        $this->water = $water;

        return $this;
    }

    public function getFire(): ?int
    {
        return $this->fire;
    }

    public function setFire(int $fire): self
    {
        $this->fire = $fire;

        return $this;
    }

    public function getWind(): ?int
    {
        return $this->wind;
    }

    public function setWind(int $wind): self
    {
        $this->wind = $wind;

        return $this;
    }

    public function getSpirit(): ?int
    {
        return $this->spirit;
    }

    public function setSpirit(int $spirit): self
    {
        $this->spirit = $spirit;

        return $this;
    }

    public function getFertilizer(): int
    {
        return $this->fertilizer;
    }

    public function setFertilizer(int $fertilizer): self
    {
        $this->fertilizer = $fertilizer;

        return $this;
    }

    public function getNonTransferable(): ?bool
    {
        return $this->nonTransferable;
    }

    public function setNonTransferable(bool $nonTransferable): self
    {
        $this->nonTransferable = $nonTransferable;

        return $this;
    }

    public function getPlant(): ?ItemPlant
    {
        return $this->plant;
    }

    public function setPlant(?ItemPlant $plant): self
    {
        $this->plant = $plant;

        return $this;
    }
}
