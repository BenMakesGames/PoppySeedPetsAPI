<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'fertilizer_idx', columns: ['fertilizer'])]
#[ORM\Entity]
class Item
{
    #[Groups(["itemEncyclopedia", "myPet", "marketItem", "myInventory", "itemTypeahead", "museum"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "museum", "marketItem", "knownRecipe", "mySeeds", "fireplaceMantle", "fireplaceFuel", "myBeehive", "itemTypeahead", "guildEncyclopedia", "greenhouseFertilizer", "dragonTreasure", "petActivityLogAndPublicPet", "myBids", "myHollowEarthTiles", "myLetters", "zoologistCatalog"])]
    #[ORM\Column(type: 'string', length: 45, unique: true)]
    private $name;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[Groups(["myPet", "myInventory", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "museum", "marketItem", "knownRecipe", "mySeeds", "hollowEarth", "fireplaceMantle", "fireplaceFuel", "myBeehive", "petGroupDetails", "itemTypeahead", "guildEncyclopedia", "greenhouseFertilizer", "dragonTreasure", "petActivityLogAndPublicPet", "myBids", "myHollowEarthTiles", "helperPet"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $image;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\Column(type: 'json', nullable: true)]
    private $useActions = [];

    #[Groups(["myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet"])]
    #[ORM\OneToOne(targetEntity: 'App\Entity\ItemTool')]
    private $tool;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\OneToOne(targetEntity: 'App\Entity\ItemFood', cascade: ['persist', 'remove'])]
    private $food;

    #[ORM\Column(type: 'integer')]
    private $fertilizer = 0;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Plant', inversedBy: 'item', cascade: ['persist', 'remove'])]
    private $plant;

    #[ORM\OneToMany(targetEntity: 'App\Entity\MuseumItem', mappedBy: 'item')]
    private $museumDonations;

    #[ORM\OneToMany(targetEntity: Inventory::class, mappedBy: 'item')]
    private $inventory;

    #[Groups(["myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails", "helperPet"])]
    #[ORM\OneToOne(targetEntity: 'App\Entity\ItemHat', inversedBy: 'item', cascade: ['persist', 'remove'])]
    private $hat;

    #[ORM\Column(type: 'integer')]
    private $fuel = 0;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\Column(type: 'smallint')]
    private $recycleValue = 0;

    #[Groups(["myInventory", "marketItem", "itemEncyclopedia"])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Enchantment')]
    private $enchants;

    #[ORM\OneToOne(targetEntity: ItemGrammar::class, mappedBy: 'item', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private $grammar;

    #[Groups(["myInventory", "marketItem", "itemEncyclopedia"])]
    #[ORM\ManyToOne(targetEntity: Spice::class)]
    private $spice;

    #[Groups(["dragonTreasure"])]
    #[ORM\OneToOne(targetEntity: ItemTreasure::class, cascade: ['persist', 'remove'])]
    private $treasure;

    #[ORM\Column(type: 'boolean')]
    private $isBug = false;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\ManyToOne(targetEntity: HollowEarthTileCard::class)]
    private $hollowEarthTileCard;

    #[Groups(["itemEncyclopedia"])]
    #[ORM\ManyToMany(targetEntity: ItemGroup::class, mappedBy: 'items')]
    private $itemGroups;

    #[ORM\Column(type: 'boolean')]
    private $cannotBeThrownOut = false;

    #[Groups(["myDonatableInventory"])]
    #[ORM\Column(type: 'smallint')]
    private $museumPoints;

    #[ORM\OneToMany(targetEntity: MarketListing::class, mappedBy: 'item')]
    private $marketListings;

    public function __construct()
    {
        $this->itemGroups = new ArrayCollection();
        $this->marketListings = new ArrayCollection();
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

    #[Groups(["myBeehive", "itemEncyclopedia", "myLetters"])]
    public function getNameWithArticle(): string
    {
        if($this->getGrammar() && $this->getGrammar()->getArticle())
            return $this->getGrammar()->getArticle() . ' ' . $this->getName();
        else
            return $this->getName();
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

    #[Groups(["fireplaceFuel"])]
    public function getFuelRating(): int
    {
        return (int)(($this->fuel / 1440) * 10);
    }

    #[Groups(["myInventory", "itemEncyclopedia"])]
    public function getGreenhouseType(): ?string
    {
        return $this->getPlant() === null ? null : $this->getPlant()->getType();
    }

    #[Groups(["myInventory", "itemEncyclopedia"])]
    public function getIsFlammable(): bool
    {
        return $this->getFuel() > 0;
    }

    #[Groups(["myInventory", "itemEncyclopedia"])]
    public function getIsFertilizer(): bool
    {
        return $this->getFertilizer() > 0;
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

    public function getEnchants(): ?Enchantment
    {
        return $this->enchants;
    }

    public function setEnchants(?Enchantment $enchants): self
    {
        $this->enchants = $enchants;

        return $this;
    }

    public function getGrammar(): ?ItemGrammar
    {
        return $this->grammar;
    }

    public function setGrammar(ItemGrammar $grammar): self
    {
        $this->grammar = $grammar;

        // set the owning side of the relation if necessary
        if ($grammar->getItem() !== $this) {
            $grammar->setItem($this);
        }

        return $this;
    }

    public function getSpice(): ?Spice
    {
        return $this->spice;
    }

    public function setSpice(?Spice $spice): self
    {
        $this->spice = $spice;

        return $this;
    }

    public function getTreasure(): ?ItemTreasure
    {
        return $this->treasure;
    }

    public function setTreasure(ItemTreasure $treasure): self
    {
        $this->treasure = $treasure;

        return $this;
    }

    public function getIsBug(): bool
    {
        return $this->isBug;
    }

    public function setIsBug(bool $isBug): self
    {
        $this->isBug = $isBug;

        return $this;
    }

    public function getHollowEarthTileCard(): ?HollowEarthTileCard
    {
        return $this->hollowEarthTileCard;
    }

    public function setHollowEarthTileCard(?HollowEarthTileCard $hollowEarthTileCard): self
    {
        $this->hollowEarthTileCard = $hollowEarthTileCard;

        return $this;
    }

    /**
     * @return Collection|ItemGroup[]
     */
    public function getItemGroups(): Collection
    {
        return $this->itemGroups;
    }

    public function addItemGroup(ItemGroup $itemGroup): self
    {
        if (!$this->itemGroups->contains($itemGroup)) {
            $this->itemGroups[] = $itemGroup;
            $itemGroup->addItem($this);
        }

        return $this;
    }

    public function removeItemGroup(ItemGroup $itemGroup): self
    {
        if ($this->itemGroups->removeElement($itemGroup)) {
            $itemGroup->removeItem($this);
        }

        return $this;
    }

    public function hasItemGroup(string $itemGroupName): bool
    {
        foreach($this->itemGroups as $group)
        {
            /** @var ItemGroup $group */
            if($group->getName() === $itemGroupName)
                return true;
        }

        return false;
    }

    public function getCannotBeThrownOut(): ?bool
    {
        return $this->cannotBeThrownOut;
    }

    public function setCannotBeThrownOut(bool $cannotBeThrownOut): self
    {
        $this->cannotBeThrownOut = $cannotBeThrownOut;

        return $this;
    }

    public function getMuseumPoints(): ?int
    {
        return $this->museumPoints;
    }

    public function setMuseumPoints(int $museumPoints): self
    {
        $this->museumPoints = $museumPoints;

        return $this;
    }

    /**
     * @return Collection<int, MarketListing>
     */
    public function getMarketListings(): Collection
    {
        return $this->marketListings;
    }

    public function addMarketListing(MarketListing $marketListing): self
    {
        if (!$this->marketListings->contains($marketListing)) {
            $this->marketListings[] = $marketListing;
            $marketListing->setItem($this);
        }

        return $this;
    }
}
