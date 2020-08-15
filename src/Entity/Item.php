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
     * @Groups({"itemEncyclopedia", "myPet", "marketItem", "myInventory", "itemTypeahead"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, unique=true)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "museum", "marketItem", "knownRecipe", "mySeeds", "fireplaceMantle", "fireplaceFuel", "myBeehive", "itemTypeahead", "guildEncyclopedia", "greenhouseFertilizer"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "museum", "marketItem", "knownRecipe", "mySeeds", "hollowEarth", "fireplaceMantle", "fireplaceFuel", "myBeehive", "petGroupDetails", "itemTypeahead", "guildEncyclopedia", "greenhouseFertilizer"})
     */
    private $image;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $useActions = [];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemTool")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails"})
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
    private $fertilizer = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Plant", inversedBy="item", cascade={"persist", "remove"})
     */
    private $plant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MuseumItem", mappedBy="item")
     */
    private $museumDonations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventory", mappedBy="item")
     */
    private $inventory;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemHat", inversedBy="item", cascade={"persist", "remove"})
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails"})
     */
    private $hat;

    /**
     * @ORM\Column(type="integer")
     */
    private $fuel = 0;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $recycleValue = 0;

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

    public function getFertilizer(): int
    {
        return $this->fertilizer;
    }

    public function setFertilizer(int $fertilizer): self
    {
        $this->fertilizer = $fertilizer;

        return $this;
    }

    public function getPlant(): ?Plant
    {
        return $this->plant;
    }

    public function setPlant(?Plant $plant): self
    {
        $this->plant = $plant;

        return $this;
    }

    public function getHat(): ?ItemHat
    {
        return $this->hat;
    }

    public function setHat(?ItemHat $hat): self
    {
        $this->hat = $hat;

        return $this;
    }

    public function getFuel(): ?int
    {
        return $this->fuel;
    }

    public function setFuel(int $fuel): self
    {
        $this->fuel = $fuel;

        return $this;
    }

    /**
     * @Groups({"fireplaceFuel"})
     */
    public function getFuelRating(): int
    {
        return (int)(($this->fuel / 1440) * 10);
    }

    /**
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    public function getGreenhouseType(): ?string
    {
        return $this->getPlant() === null ? null : $this->getPlant()->getType();
    }

    /**
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    public function getIsFlammable(): bool
    {
        return $this->getFuel() > 0;
    }

    /**
     * @Groups({"myGreenhouse"})
     */
    public function getFertilizerRating(): int
    {
        if($this->fertilizer >= 200)
            return 10;
        else if($this->fertilizer >= 100)
            return 9;
        else if($this->fertilizer >= 50)
            return 8;
        else if($this->fertilizer >= 40)
            return 7;
        else if($this->fertilizer >= 30)
            return 6;
        else if($this->fertilizer >= 20)
            return 5;
        else if($this->fertilizer >= 14)
            return 4;
        else if($this->fertilizer >= 8)
            return 3;
        else if($this->fertilizer >= 4)
            return 2;
        else
            return 1;
    }

    public function getRecycleValue(): int
    {
        return $this->recycleValue;
    }

    public function setRecycleValue(int $recycleValue): self
    {
        $this->recycleValue = $recycleValue;

        return $this;
    }

}
