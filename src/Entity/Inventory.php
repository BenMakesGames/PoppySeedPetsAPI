<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Functions\InventoryModifierFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'modified_on_idx', columns: ['modified_on'])]
#[ORM\Index(name: 'location_idx', columns: ['location'])]
#[ORM\Index(name: 'full_item_name_idx', columns: ['full_item_name'])]
#[ORM\Entity(repositoryClass: 'App\Repository\InventoryRepository')]
class Inventory
{
    public const CONSUMABLE_LOCATIONS = [
        LocationEnum::HOME,
        LocationEnum::BASEMENT
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["myPet", 'houseSitterPet', "myInventory", "greenhouseFertilizer", "mySeeds", "fireplaceFuel", "dragonTreasure", "myHollowEarthTiles"])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'inventory')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["myPet", 'houseSitterPet', "myInventory", "userPublicProfile", "petPublicProfile", "marketItem", "greenhouseFertilizer", "mySeeds", "hollowEarth", "fireplaceMantle", "fireplaceFuel", "petGroupDetails", "dragonTreasure", "myHollowEarthTiles", "helperPet"])]
    private $item;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["myInventory"])]
    private $createdOn;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["myInventory"])]
    private $modifiedOn;

    #[ORM\Column(type: 'json')]
    #[Groups(["myInventory", "fireplaceMantle"])]
    private $comments = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(["myInventory"])]
    private $createdBy;

    #[ORM\OneToOne(targetEntity: Pet::class, mappedBy: 'tool')]
    private $holder;

    #[ORM\Column(type: 'smallint')]
    private $location = LocationEnum::HOME;

    #[ORM\OneToOne(targetEntity: Pet::class, mappedBy: 'hat')]
    private $wearer;

    #[ORM\Column(type: 'boolean')]
    #[Groups(["myInventory"])]
    private bool $lockedToOwner = false;

    #[ORM\OneToOne(targetEntity: 'App\Entity\LunchboxItem', mappedBy: 'inventoryItem', cascade: ['remove'])]
    private $lunchboxItem;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Enchantment')]
    #[Groups(["myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", 'houseSitterPet', "fireplaceMantle", "dragonTreasure", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"])]
    private $enchantment;

    #[ORM\ManyToOne(targetEntity: Spice::class)]
    #[Groups(["myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", 'houseSitterPet', "fireplaceMantle", "dragonTreasure"])]
    private $spice;

    #[ORM\Column(type: 'string', length: 100)]
    private $fullItemName;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[Groups(["myInventory", "myPet", 'houseSitterPet', "fireplaceMantle", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet", "fireplaceFuel", "dragonTreasure"])]
    private $illusion;

    #[ORM\OneToOne(mappedBy: 'inventory', cascade: ['persist', 'remove'])]
    private ?InventoryForSale $forSale = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
        $this->modifiedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        if($this->item !== null) throw new \InvalidArgumentException('$item has already been set; use changeItem, instead!');

        $this->item = $item;

        $this->fullItemName = InventoryModifierFunctions::getNameWithModifiers($this);

        return $this;
    }

    public function changeItem(Item $item): self
    {
        $this->item = $item;

        $this->fullItemName = InventoryModifierFunctions::getNameWithModifiers($this);

        // if the item changes, we need to make sure it can still be worn/held, and unequip it if not
        if($this->getWearer() && !$item->getHat())
        {
            $this
                ->setLocation(LocationEnum::HOME)
                ->getWearer()->setHat(null)
            ;
        }

        if($this->getHolder() && !$item->getTool())
        {
            $this
                ->setLocation(LocationEnum::HOME)
                ->getHolder()->setTool(null)
            ;
        }

        $this->setModifiedOn();

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function changeOwner(User $owner, string $comment, EntityManagerInterface $em): self
    {
        if(!$owner)
            throw new \Exception("Cannot change item owner; item has no owner.");

        $this->addComment($comment);

        $this->owner = $owner;

        $this->setModifiedOn();

        if($this->getForSale())
        {
            $em->remove($this->getForSale());
            $this->forSale = null;
        }

        if($this->getLunchboxItem())
        {
            $em->remove($this->getLunchboxItem());
            $this->lunchboxItem = null;
        }

        if($this->getHolder()) $this->getHolder()->setTool(null);
        if($this->getWearer()) $this->getWearer()->setHat(null);

        return $this;
    }

    public function setOwner(User $owner): self
    {
        if($this->owner)
            throw new \Exception("Cannot set item owner; item already has an owner.");

        $this->owner = $owner;

        return $this;
    }

    public function forceSetOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getModifiedOn(): \DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    public function setModifiedOn(): self
    {
        $this->modifiedOn = new \DateTimeImmutable();

        return $this;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(string $comment): self
    {
        $this->comments[] = trim($comment);

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getHolder(): ?Pet
    {
        return $this->holder;
    }

    public function setHolder(?Pet $pet): self
    {
        $this->holder = $pet;

        // set (or unset) the owning side of the relation if necessary
        $newTool = $pet === null ? null : $this;
        if ($newTool !== $pet->getTool()) {
            $pet->setTool($newTool);
        }

        return $this;
    }

    public function getLocation(): int
    {
        return $this->location;
    }

    public function setLocation(int $location): self
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

        $this->location = $location;

        $this->setModifiedOn();

        return $this;
    }

    public function getWearer(): ?Pet
    {
        return $this->wearer;
    }

    public function setWearer(?Pet $wearer): self
    {
        $this->wearer = $wearer;

        // set (or unset) the owning side of the relation if necessary
        $newHat = $wearer === null ? null : $this;
        if ($newHat !== $wearer->getHat()) {
            $wearer->setHat($newHat);
        }

        return $this;
    }

    public function getLockedToOwner(): ?bool
    {
        return $this->lockedToOwner;
    }

    public function setLockedToOwner(bool $lockedToOwner): self
    {
        $this->lockedToOwner = $lockedToOwner;

        return $this;
    }

    public function getLunchboxItem(): ?LunchboxItem
    {
        return $this->lunchboxItem;
    }

    public function setLunchboxItem(LunchboxItem $lunchboxItem): self
    {
        $this->lunchboxItem = $lunchboxItem;

        // set the owning side of the relation if necessary
        if ($lunchboxItem->getInventoryItem() !== $this) {
            $lunchboxItem->setInventoryItem($this);
        }

        return $this;
    }

    public function getEnchantment(): ?Enchantment
    {
        return $this->enchantment;
    }

    public function setEnchantment(?Enchantment $enchantment): self
    {
        $this->enchantment = $enchantment;

        $this->fullItemName = InventoryModifierFunctions::getNameWithModifiers($this);

        return $this;
    }

    public function providesLight(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getProvidesLight()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getProvidesLight())
        ;
    }

    public function protectsFromHeat(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getProtectionFromHeat()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getProtectionFromHeat())
        ;
    }

    public function sexDriveBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getSexDrive() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getSexDrive() : 0)
        ;
    }

    public function natureBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getNature() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getNature() : 0)
        ;
    }

    public function stealthBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getStealth() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getStealth() : 0)
        ;
    }

    public function rangedOnly(): bool
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getIsRanged() : 0) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getIsRanged())
        ;
    }

    public function brawlBonus($allowRanged = true): int
    {
        if(!$allowRanged && $this->rangedOnly())
            return 0;

        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getBrawl() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getBrawl() : 0)
        ;
    }

    public function craftsBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getCrafts() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getCrafts() : 0)
        ;
    }

    public function arcanaBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getArcana() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getArcana() : 0)
        ;
    }

    public function fishingBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getFishing() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getFishing() : 0)
        ;
    }

    public function musicBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getMusic() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getMusic() : 0)
        ;
    }

    public function smithingBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getSmithing() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getSmithing() : 0)
        ;
    }

    public function gatheringBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getGathering() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getGathering() : 0)
        ;
    }

    public function scienceBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getScience() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getScience() : 0)
        ;
    }

    public function climbingBonus(): int
    {
        return
            ($this->getItem()->getTool() ? $this->getItem()->getTool()->getClimbing() : 0) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getClimbing() : 0)
        ;
    }

    public function focusesSkill(string $skill): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getFocusSkill() === $skill) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getFocusSkill() === $skill)
        ;
    }

    public function increasesPooping(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getIncreasesPooping()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getIncreasesPooping())
        ;
    }

    public function canBeNibbled(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getCanBeNibbled()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getCanBeNibbled())
        ;
    }

    public function isDreamcatcher(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getDreamcatcher()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getDreamcatcher())
        ;
    }

    public function isGrayscaling(): bool
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getIsGrayscaling()) ||
            ($this->getEnchantment() && $this->getEnchantment()->getEffects()->getIsGrayscaling())
        ;
    }

    public function isGreenifying(): bool
    {
        return $this->getItem()->getName() === '5-leaf Clover';
    }

    public function socialEnergyModifier(): int
    {
        return
            ($this->getItem()->getTool() && $this->getItem()->getTool()->getSocialEnergyModifier()) +
            ($this->getEnchantment() ? $this->getEnchantment()->getEffects()->getSocialEnergyModifier() : 0)
        ;
    }

    public function getSpice(): ?Spice
    {
        return $this->spice;
    }

    public function setSpice(?Spice $spice): self
    {
        if($spice && $this->getItem()->getSpice())
            return $this;

        $this->spice = $spice;

        $this->fullItemName = InventoryModifierFunctions::getNameWithModifiers($this);

        return $this;
    }

    public function getTotalFertilizerValue()
    {
        $value = $this->getItem()->getFertilizer();

        if($this->getSpice() && $this->getSpice()->getEffects())
        {
            $value += $this->getSpice()->getEffects()->getFood();
            $value += $this->getSpice()->getEffects()->getLove();
        }

        return $value;
    }

    #[Groups(["greenhouseFertilizer"])]
    public function getFertilizerRating(): int
    {
        $totalFertilizerValue = $this->getTotalFertilizerValue();

        if($totalFertilizerValue >= 200)
            return 10;
        else if($totalFertilizerValue >= 100)
            return 9;
        else if($totalFertilizerValue >= 50)
            return 8;
        else if($totalFertilizerValue >= 40)
            return 7;
        else if($totalFertilizerValue >= 30)
            return 6;
        else if($totalFertilizerValue >= 20)
            return 5;
        else if($totalFertilizerValue >= 14)
            return 4;
        else if($totalFertilizerValue >= 8)
            return 3;
        else if($totalFertilizerValue >= 4)
            return 2;
        else if($totalFertilizerValue > 0)
            return 1;
        else
            return 0;
    }

    public function getFullItemName(): ?string
    {
        return $this->fullItemName;
    }

    public function getIllusion(): ?Item
    {
        return $this->illusion;
    }

    public function setIllusion(?Item $illusion): self
    {
        $this->illusion = $illusion;

        return $this;
    }

    public function getForSale(): ?InventoryForSale
    {
        return $this->forSale;
    }

    public function setForSale(InventoryForSale $forSale): static
    {
        // set the owning side of the relation if necessary
        if ($forSale->getInventory() !== $this) {
            $forSale->setInventory($this);
        }

        $this->forSale = $forSale;

        return $this;
    }

    #[Groups(["myInventory", "fireplaceFuel", "myGreenhouse", "myPet", 'houseSitterPet', "dragonTreasure", "myHollowEarthTiles"])]
    public function getSellPrice()
    {
        return $this->getForSale()?->getSellPrice();
    }
}
