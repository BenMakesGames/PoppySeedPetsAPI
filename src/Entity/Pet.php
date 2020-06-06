<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LoveLanguageEnum;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetPregnancyStyleEnum;
use App\Enum\RelationshipEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\NumberFunctions;
use App\Service\PetExperienceService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="park_event_type_idx", columns={"park_event_type"}),
 *     @ORM\Index(name="park_event_order_idx", columns={"park_event_order"}),
 *     @ORM\Index(name="time_idx", columns={"time"}),
 *     @ORM\Index(name="social_energy_idx", columns={"social_energy"}),
 *     @ORM\Index(name="in_daycare_idx", columns={"in_daycare"}),
 *     @ORM\Index(name="name_idx", columns={"name"}),
 * })
 */
class Pet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "myInventory", "parkEvent", "petFriend", "fireplaceFuel", "petGroupDetails", "spiritCompanionPublicProfile"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pets")
     * @Groups({"petPublicProfile", "parkEvent"})
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "myInventory", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails", "spiritCompanionPublicProfile"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", name="`time`")
     */
    private $time = 60;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeSpent = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $food = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $safety = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $love = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $esteem = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $experience = 0;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails"})
     */
    private $colorB;

    /**
     * @ORM\Column(type="integer")
     */
    private $junk = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $alcohol = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"myPet", "petPublicProfile"})
     */
    private $birthDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $stomachSize;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastInteracted;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PetSkills", inversedBy="pet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet"})
     */
    private $skills;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PetSpecies")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails"})
     */
    private $species;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", inversedBy="holder")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $tool;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $favoriteFlavor;

    /**
     * @ORM\Column(type="text")
     * @Groups({"myPet"})
     */
    private $note = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $affectionPoints = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myPet"})
     */
    private $affectionLevel = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myPet"})
     */
    private $affectionRewardsClaimed = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SpiritCompanion", inversedBy="pet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"myPet", "parkEvent", "hollowEarth", "petPublicProfile", "petGroupDetails"})
     */
    private $spiritCompanion;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"myPet"})
     */
    private $lastParkEvent;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     * @Groups({"myPet"})
     */
    private $parkEventType;

    /**
     * @ORM\Column(type="integer")
     */
    private $parkEventOrder = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PetRelationship", mappedBy="pet", orphanRemoval=true)
     */
    private $petRelationships;

    /**
     * @ORM\Column(type="integer")
     */
    private $caffeine = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $psychedelic = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $poison = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StatusEffect", mappedBy="pet", orphanRemoval=true)
     */
    private $statusEffects;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"petPublicProfile"})
     */
    private $inDaycare = false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $poly;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sexDrive;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PetBaby", inversedBy="parent", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petFriend", "petGroupDetails"})
     */
    private $pregnancy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="motheredPets")
     */
    private $mom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="mom")
     */
    private $motheredPets;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="fatheredPets")
     */
    private $dad;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="dad")
     */
    private $fatheredPets;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Merit")
     * @Groups({"myPet", "petPublicProfile", "petGroupDetails", "parkEvent"})
     */
    private $merits;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet"})
     */
    private $isFertile = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", inversedBy="wearer")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $hat;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups({"myPet", "petPublicProfile"})
     */
    private $costume = '';

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PetActivityStats", mappedBy="pet", cascade={"persist", "remove"})
     */
    private $petActivityStats;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\PetGroup", mappedBy="members")
     * @Groups({"petPublicProfile"})
     */
    private $groups;

    /**
     * @ORM\Column(type="smallint")
     */
    private $extroverted;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $loveLanguage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isGrandparent = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $claimedGrandparentMerit = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\GuildMembership", mappedBy="pet", cascade={"persist", "remove"})
     * @Groups({"petPublicProfile"})
     */
    private $guildMembership;

    /**
     * @ORM\Column(type="integer")
     */
    private $revealedFavoriteFlavor = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $affectionAdventures = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LunchboxItem", mappedBy="pet")
     * @Groups({"myPet"})
     */
    private $lunchboxItems;

    /**
     * @ORM\Column(type="integer")
     */
    private $socialEnergy = 0;

    public function __construct()
    {
        $this->birthDate = new \DateTimeImmutable();
        $this->lastInteracted = (new \DateTimeImmutable())->modify('-3 days');
        $this->stomachSize = mt_rand(16, 30);
        $this->petRelationships = new ArrayCollection();
        $this->statusEffects = new ArrayCollection();
        $this->extroverted = mt_rand(-1, 1);

        $this->socialEnergy = ceil(PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * (4 + $this->extroverted) / 4);

        // 10% poly; 10% flexible; 80% monogamous
        if(mt_rand(1, 10) === 1)
            $this->poly = 1;
        else
            $this->poly = mt_rand(1, 9) === 1 ? 0 : -1;

        if(mt_rand(1, 5) > 1)
            $this->sexDrive = 1; // 80% sexual
        else if(mt_rand(1, 10) === 1)
            $this->sexDrive = -1; // 2% asexual
        else
            $this->sexDrive = 0; // 18% flexible

        $this->motheredPets = new ArrayCollection();
        $this->fatheredPets = new ArrayCollection();
        $this->merits = new ArrayCollection();
        $this->groups = new ArrayCollection();

        $this->loveLanguage = LoveLanguageEnum::getRandomValue();
        $this->lunchboxItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
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

    public function getTime(): int
    {
        return $this->time;
    }

    public function spendTime(int $amount): self
    {
        $this->time -= $amount;
        $this->timeSpent += $amount;

        return $this;
    }

    public function getTimeSpent(): int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }

    public function setNeeds(int $food, int $safety): self
    {
        $this->food = $food;
        $this->safety = $safety;

        return $this;
    }

    public function getFood(): int
    {
        return $this->food;
    }

    public function increaseFood(int $amount): self
    {
        if($amount === 0) return $this;

        $this->food = NumberFunctions::constrain(
            $this->food + $amount,
            -16,                                            // minimum
            $this->getStomachSize() - max(0, $this->junk)   // maximum
        );

        return $this;
    }

    public function getSafety(): int
    {
        return $this->safety;
    }

    public function increaseSafety(int $amount): self
    {
        if($amount === 0) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount === 0) return $this;

        $this->safety = NumberFunctions::constrain($this->safety + $amount, $this->getMinSafety(), $this->getMaxSafety());

        return $this;
    }

    public function getMinSafety(): int
    {
        return -16;
    }

    public function getMaxSafety(): int
    {
        return 24;
    }

    public function getLove(): int
    {
        return $this->love;
    }

    public function increaseLove(int $amount): self
    {
        if($amount === 0) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;
        if($this->getSafety() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount === 0) return $this;

        $this->love = NumberFunctions::constrain($this->love + $amount, $this->getMinLove(), $this->getMaxLove());

        return $this;
    }

    public function getMinLove(): int
    {
        return -16;
    }

    public function getMaxLove(): int
    {
        return 24;
    }

    public function getEsteem(): int
    {
        return $this->esteem;
    }

    public function increaseEsteem(int $amount): self
    {
        if($amount === 0) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;
        if($this->getSafety() + $this->getAlcohol() < 0) $divisor++;
        if($this->getLove() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount === 0) return $this;

        $this->esteem = NumberFunctions::constrain($this->esteem + $amount, $this->getMinEsteem(), $this->getMaxEsteem());

        return $this;
    }

    public function getMinEsteem(): int
    {
        return -16;
    }

    public function getMaxEsteem(): int
    {
        return 24;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function increaseExperience(int $amount): self
    {
        $this->experience += $amount;

        return $this;
    }

    public function decreaseExperience(int $amount): self
    {
        $this->experience -= $amount;

        return $this;
    }

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $color): self
    {
        $this->colorA = $color;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $color): self
    {
        $this->colorB = $color;

        return $this;
    }

    public function getJunk(): int
    {
        return $this->junk;
    }

    public function increaseJunk(int $amount): self
    {
        if($amount === 0) return $this;

        $this->junk = NumberFunctions::constrain($this->junk + $amount, 0, $this->getStomachSize() - max(0, $this->food));

        return $this;
    }

    public function getAlcohol(): int
    {
        return $this->alcohol;
    }

    public function increaseAlcohol(int $amount): self
    {
        if($amount === 0) return $this;

        $this->alcohol = NumberFunctions::constrain($this->alcohol + $amount, 0, 16);

        return $this;
    }

    public function increaseCaffeine(int $amount): self
    {
        if($amount === 0) return $this;

        $this->caffeine = NumberFunctions::constrain($this->caffeine + $amount, 0, 16);

        return $this;
    }

    public function increasePsychedelic(int $amount): self
    {
        if($amount === 0) return $this;

        $this->psychedelic = NumberFunctions::constrain($this->psychedelic + $amount, 0, $this->getMaxPsychedelic());

        return $this;
    }

    public function getMaxPsychedelic(): int
    {
        return 16;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getFull(): string
    {
        $fullness = ($this->getFood() + $this->getJunk()) / $this->getStomachSize();

        if($fullness >= 0.75)
        {
            if(substr($this->getSpecies()->getImage(), 5) === 'fish/')
                return 'stuffed to the gills';
            else
                return 'stuffed';
        }
        else if($fullness >= 0.50)
            return 'full';
        else if($fullness >= 0.25)
            return 'sated';
        else if($fullness >= 0)
            return '...';
        else if($fullness >= -0.25)
            return 'peckish';
        else if($fullness >= -0.50)
            return 'hungry';
        else if($fullness >= -0.75)
            return 'very hungry';
        else
            return 'starving';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getSafe(): string
    {
        if($this->getSafety() >= 16)
            return 'invincible';
        else if($this->getSafety() >= 8)
            return 'safe';
        else if($this->getSafety() >= 0)
            return '...';
        else if($this->getSafety() >= -12)
            return 'on edge';
        else
            return 'terrified';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getLoved(): string
    {
        if($this->getLove() >= 16)
            return 'very loved';
        else if($this->getLove() >= 8)
            return 'loved';
        else if($this->getLove() >= 0)
            return '...';
        else if($this->getLove() >= -12)
            return 'lonely';
        else
            return 'hated';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getEsteemed(): string
    {
        if($this->getEsteem() >= 16)
            return 'amazing';
        else if($this->getEsteem() >= 8)
            return 'accomplished';
        else if($this->getEsteem() >= 0)
            return '...';
        else if($this->getEsteem() >= -12)
            return 'useless';
        else
            return 'depressed';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getAlcoholLevel(): string
    {
        if($this->getAlcohol() > 16)
            return 'crazy-high';
        else if($this->getAlcohol() > 8)
            return 'high';
        else if($this->getAlcohol() > 0)
            return 'low';
        else
            return 'none';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getHallucinogenLevel(): string
    {
        if($this->getPsychedelic() > 16)
            return 'crazy-high';
        else if($this->getPsychedelic() > 8)
            return 'high';
        else if($this->getPsychedelic() > 0)
            return 'low';
        else
            return 'none';
    }

    /**
     * @Groups({"myPet"})
     */
    public function getPoisonLevel(): string
    {
        if($this->getPoison() > 16)
            return 'crazy-high';
        else if($this->getPoison() > 8)
            return 'high';
        else if($this->getPoison() > 0)
            return 'low';
        else
            return 'none';
    }

    public function getStomachSize(): int
    {
        return
            $this->stomachSize +
            ($this->hasMerit(MeritEnum::BLACK_HOLE_TUM) ? 6 : 0) +
            ($this->hasMerit(MeritEnum::GOURMAND) ? 4 : 0)
        ;
    }

    public function getLastInteracted(): \DateTimeImmutable
    {
        return $this->lastInteracted;
    }

    public function setLastInteracted(\DateTimeImmutable $lastInteracted): self
    {
        $this->lastInteracted = $lastInteracted;

        return $this;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getCanInteract(): bool
    {
        return $this->getLastInteracted() < (new \DateTimeImmutable())->modify('-4 hours');
    }

    public function getSkills(): ?PetSkills
    {
        return $this->skills;
    }

    public function setSkills(PetSkills $skills): self
    {
        $this->skills = $skills;

        return $this;
    }

    /**
     * @Groups({"myPet", "petPublicProfile"})
     */
    public function getLevel(): int
    {
        return $this->getSkills()->getTotal();
    }

    public function getExperienceToLevel(): int
    {
        return ($this->getLevel() + 1) * 15;
    }

    public function getSpecies(): ?PetSpecies
    {
        return $this->species;
    }

    public function setSpecies(?PetSpecies $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function getTool(): ?Inventory
    {
        return $this->tool;
    }

    public function setTool(?Inventory $tool): self
    {
        $this->tool = $tool;

        return $this;
    }

    public function getDexterity(): int
    {
        return
            $this->getSkills()->getDexterity() +
            ($this->hasMerit(MeritEnum::PREHENSILE_TONGUE) ? 1 : 0)
        ;
    }

    public function getStrength(): int
    {
        return
            $this->getSkills()->getStrength() +
            ($this->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0)
        ;
    }

    public function getStamina(): int
    {
        return
            $this->getSkills()->getStamina() +
            ($this->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0)
        ;
    }

    public function getIntelligence(): int
    {
        return
            $this->getSkills()->getIntelligence() +
            ($this->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0)
        ;
    }

    public function getPerception(): int
    {
        return $this->getSkills()->getPerception();
    }

    public function canSeeInTheDark(): bool
    {
        return
            $this->hasMerit(MeritEnum::DARKVISION) ||
            ($this->getTool() && $this->getTool()->getItem()->getTool()->getProvidesLight())
        ;
    }

    public function hasProtectionFromHeat(): bool
    {
        return $this->getTool() ? $this->getTool()->getItem()->getTool()->getProtectionFromHeat() : false;
    }

    public function getNature(): int
    {
        return $this->getSkills()->getNature() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getNature() : 0);
    }

    public function getBrawl($allowRanged = true): int
    {
        return
            $this->getSkills()->getBrawl() +
            // the pet has a tool, and: either it's not ranged, or ranged weapons are allowed
            (($this->getTool() && (!$this->getTool()->getItem()->getTool()->getIsRanged() || $allowRanged)) ? $this->getTool()->getItem()->getTool()->getBrawl() : 0)
        ;
    }

    public function getStealth(): int
    {
        return
            $this->getSkills()->getStealth() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getStealth() : 0) +
            ($this->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION) ? 1 : 0) +
            ($this->hasMerit(MeritEnum::SPECTRAL) ? 1 : 0)
        ;
    }

    public function getCrafts(): int
    {
        return $this->getSkills()->getCrafts() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getCrafts() : 0);
    }

    public function getUmbra(): int
    {
        return
            $this->getSkills()->getUmbra() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getUmbra() : 0) +
            ceil($this->getPsychedelic() * 5 / $this->getMaxPsychedelic())
        ;
    }

    public function getFishing(): int
    {
        // no bonus for the casting no reflection merit; we grant that bonus elsewhere
        return
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getFishing() : 0)
        ;
    }

    public function getMusic(): int
    {
        return $this->getSkills()->getMusic() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getMusic() : 0);
    }

    public function getSmithing(): int
    {
        return $this->getTool() ? $this->getTool()->getItem()->getTool()->getSmithing() : 0;
    }

    public function getGathering(): int
    {
        return $this->getTool() ? $this->getTool()->getItem()->getTool()->getGathering() : 0;
    }

    public function getScience(): int
    {
        return $this->getSkills()->getScience() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getScience() : 0);
    }

    public function getClimbing(): int
    {
        return $this->getTool() ? $this->getTool()->getItem()->getTool()->getClimbing() : 0;
    }

    public function getFavoriteFlavor(): string
    {
        return $this->favoriteFlavor;
    }

    public function setFavoriteFlavor(string $favoriteFlavor): self
    {
        if(!FlavorEnum::isAValue($favoriteFlavor))
            throw new EnumInvalidValueException(FlavorEnum::class, $favoriteFlavor);

        $this->favoriteFlavor = $favoriteFlavor;

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getAffectionPoints(): int
    {
        return $this->affectionPoints;
    }

    public function getAffectionLevel(): int
    {
        return $this->affectionLevel;
    }

    public function increaseAffectionPoints(int $amount): self
    {
        $this->affectionPoints += $amount;

        while($this->affectionPoints >= $this->getAffectionPointsToLevel())
        {
            $this->affectionPoints -= $this->getAffectionPointsToLevel();
            $this->affectionLevel++;
        }

        return $this;
    }

    public function getAffectionPointsToLevel(): int
    {
        return ($this->getAffectionLevel() + 1) * 50;
    }

    public function getAffectionRewardsClaimed(): int
    {
        return $this->affectionRewardsClaimed;
    }

    public function increaseAffectionRewardsClaimed(): self
    {
        $this->affectionRewardsClaimed++;

        return $this;
    }

    public function decreaseAffectionRewardsClaimed(): self
    {
        if($this->affectionRewardsClaimed > 0)
            $this->affectionRewardsClaimed--;

        return $this;
    }

    public function getSpiritCompanion(): ?SpiritCompanion
    {
        return $this->spiritCompanion;
    }

    public function setSpiritCompanion(?SpiritCompanion $spiritCompanion): self
    {
        $this->spiritCompanion = $spiritCompanion;

        return $this;
    }

    public function getLastParkEvent(): ?\DateTimeImmutable
    {
        return $this->lastParkEvent;
    }

    public function setLastParkEvent(): self
    {
        $this->lastParkEvent = new \DateTimeImmutable();

        return $this;
    }

    public function getParkEventType(): ?string
    {
        return $this->parkEventType;
    }

    public function setParkEventType(?string $parkEventType): self
    {
        if($parkEventType !== null && !ParkEventTypeEnum::isAValue($parkEventType))
            throw new \InvalidArgumentException('"' . $parkEventType . '" is not a valid park event type.');

        $this->parkEventType = $parkEventType;
        $this->parkEventOrder = mt_rand(0, 2000000000);

        return $this;
    }

    /**
     * @return Collection|PetRelationship[]
     */
    public function getPetRelationships(): Collection
    {
        return $this->petRelationships;
    }

    public function addPetRelationship(PetRelationship $petRelationship): self
    {
        if (!$this->petRelationships->contains($petRelationship)) {
            $this->petRelationships[] = $petRelationship;
            $petRelationship->setPet($this);
        }

        return $this;
    }

    public function removePetRelationship(PetRelationship $petRelationship): self
    {
        if ($this->petRelationships->contains($petRelationship)) {
            $this->petRelationships->removeElement($petRelationship);
            // set the owning side to null (unless already changed)
            if ($petRelationship->getPet() === $this) {
                $petRelationship->setPet(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getHasRelationships(): bool
    {
        return count($this->petRelationships) > 0;
    }

    public function hasRelationshipWith(Pet $otherPet): bool
    {
        return $this->getRelationshipWith($otherPet) !== null;
    }

    public function getRelationshipWith(Pet $otherPet): ?PetRelationship
    {
        return ArrayFunctions::find_one($this->getPetRelationships(), function(PetRelationship $r) use($otherPet) {
            return $r->getRelationship()->getId() === $otherPet->getId();
        });
    }

    public function getRelationshipCount(): int
    {
        return count($this->getPetRelationships());
    }

    public function getLowestNeed(): string
    {
        if($this->getSafety() >= mt_rand(0, 4) && $this->getLove() >= mt_rand(0, 4) && $this->getEsteem() >= mt_rand(0, 4))
        {
            return '';
        }
        else if($this->getSafety() <= $this->getLove() + mt_rand(0, 4) && $this->getSafety() <= $this->getEsteem() + mt_rand(0, 4))
        {
            return 'safety';
        }
        else if($this->getSafety() <= $this->getEsteem() + mt_rand(2, 4))
        {
            return 'love';
        }
        else // esteem, probably
        {
            return 'esteem';
        }
    }

    public function getCaffeine(): int
    {
        return $this->caffeine;
    }

    public function getPsychedelic(): int
    {
        return $this->psychedelic;
    }

    public function getPoison(): int
    {
        return $this->poison;
    }

    public function increasePoison(int $poison): self
    {
        $this->poison = NumberFunctions::constrain($this->poison + $poison, 0, 24);

        return $this;
    }

    /**
     * @return Collection|StatusEffect[]
     */
    public function getStatusEffects(): Collection
    {
        return $this->statusEffects;
    }

    public function addStatusEffect(StatusEffect $statusEffect): self
    {
        if (!$this->statusEffects->contains($statusEffect)) {
            $this->statusEffects[] = $statusEffect;
            $statusEffect->setPet($this);
        }

        return $this;
    }

    public function removeStatusEffect(StatusEffect $statusEffect): self
    {
        if ($this->statusEffects->contains($statusEffect)) {
            $this->statusEffects->removeElement($statusEffect);
            // set the owning side to null (unless already changed)
            if ($statusEffect->getPet() === $this) {
                $statusEffect->setPet(null);
            }
        }

        return $this;
    }

    public function getStatusEffect(string $statusEffect): ?StatusEffect
    {
        if(!StatusEffectEnum::isAValue($statusEffect))
            throw new EnumInvalidValueException(StatusEffectEnum::class, $statusEffect);

        return ArrayFunctions::find_one($this->statusEffects, function(StatusEffect $se) use($statusEffect) {
            return $se->getStatus() === $statusEffect;
        });
    }

    public function hasStatusEffect(string $statusEffect): bool
    {
        return $this->getStatusEffect($statusEffect) !== null;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getStatuses(): array
    {
        return array_map(function(StatusEffect $se) { return $se->getStatus(); }, $this->statusEffects->toArray());
    }

    /**
     * @Groups({"myPet"})
     */
    public function getCanPickTalent(): ?string
    {
        if($this->getSkills()->getTalent() === null)
        {
            if($this->timeSpent >= 15000) // 15000 = ~10.4 days of minutes
                return 'talent';
        }
        else if($this->getSkills()->getExpertise() === null)
        {
            if($this->timeSpent >= 150000) // 150000 = 104 days of minutes
                return 'expertise';
        }

        return null;
    }

    public function getInDaycare(): ?bool
    {
        return $this->inDaycare;
    }

    public function setInDaycare(bool $inDaycare): self
    {
        $this->inDaycare = $inDaycare;

        return $this;
    }

    public function getPoly(): ?bool
    {
        return $this->poly;
    }

    public function setPoly(bool $poly): self
    {
        $this->poly = $poly;

        return $this;
    }

    public function getSexDrive(): int
    {
        return $this->sexDrive;
    }

    public function setSexDrive(int $sexDrive): self
    {
        $this->sexDrive = $sexDrive;

        return $this;
    }

    public function getPregnancy(): ?PetBaby
    {
        return $this->pregnancy;
    }

    public function setPregnancy(?PetBaby $pregnancy): self
    {
        $this->pregnancy = $pregnancy;

        return $this;
    }

    public function wantsSobriety(): bool
    {
        if($this->getPoison() > 6)
            return true;

        if($this->getPregnancy() !== null)
        {
            if($this->getSpecies()->getPregnancyStyle() === PetPregnancyStyleEnum::EGG)
                return $this->getPregnancy()->getGrowth() <= PetBaby::EGG_INCUBATION_TIME;
            else
                return true;
        }

        return false;
    }

    public function getMom(): ?self
    {
        return $this->mom;
    }

    public function setMom(?self $mom): self
    {
        $this->mom = $mom;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMotheredPets(): Collection
    {
        return $this->motheredPets;
    }

    public function addMotheredPet(self $motheredPet): self
    {
        if (!$this->motheredPets->contains($motheredPet)) {
            $this->motheredPets[] = $motheredPet;
            $motheredPet->setMom($this);
        }

        return $this;
    }

    public function removeMotheredPet(self $motheredPet): self
    {
        if ($this->motheredPets->contains($motheredPet)) {
            $this->motheredPets->removeElement($motheredPet);
            // set the owning side to null (unless already changed)
            if ($motheredPet->getMom() === $this) {
                $motheredPet->setMom(null);
            }
        }

        return $this;
    }

    public function getDad(): ?self
    {
        return $this->dad;
    }

    public function setDad(?self $dad): self
    {
        $this->dad = $dad;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getFatheredPets(): Collection
    {
        return $this->fatheredPets;
    }

    public function addFatheredPet(self $fatheredPet): self
    {
        if (!$this->fatheredPets->contains($fatheredPet)) {
            $this->fatheredPets[] = $fatheredPet;
            $fatheredPet->setDad($this);
        }

        return $this;
    }

    public function removeFatheredPet(self $fatheredPet): self
    {
        if ($this->fatheredPets->contains($fatheredPet)) {
            $this->fatheredPets->removeElement($fatheredPet);
            // set the owning side to null (unless already changed)
            if ($fatheredPet->getDad() === $this) {
                $fatheredPet->setDad(null);
            }
        }

        return $this;
    }

    /**
     * @return Pet[]
     */
    public function getParents(): array
    {
        $parents = [];

        if($this->mom) $parents[] = $this->mom;
        if($this->dad) $parents[] = $this->dad;

        return $parents;
    }

    public function hasMonogamousRelationship(Pet $exceptOtherPet)
    {
        if($this->getPoly() === 1)
            return false;

        foreach($this->getPetRelationships() as $relationship)
        {
            if($relationship->getRelationship()->getId() === $exceptOtherPet->getId())
                continue;

            if($relationship->getCurrentRelationship() === RelationshipEnum::MATE || $relationship->getCurrentRelationship() === RelationshipEnum::FWB)
            {
                if($this->getPoly() === 0 && $relationship->getRelationship()->getPoly() === 0)
                    return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|Merit[]
     */
    public function getMerits(): Collection
    {
        return $this->merits;
    }

    public function addMerit(Merit $merit): self
    {
        if (!$this->merits->contains($merit)) {
            $this->merits[] = $merit;
        }

        return $this;
    }

    public function removeMerit(Merit $merit): self
    {
        if ($this->merits->contains($merit)) {
            $this->merits->removeElement($merit);
        }

        return $this;
    }

    public function hasMerit(string $merit): bool
    {
        foreach($this->merits as $m)
        {
            if($m->getName() === $merit)
                return true;
        }

        return false;
    }

    public function getIsFertile(): ?bool
    {
        return $this->isFertile;
    }

    public function setIsFertile(bool $isFertile): self
    {
        $this->isFertile = $isFertile;

        return $this;
    }

    public function getHat(): ?Inventory
    {
        return $this->hat;
    }

    public function setHat(?Inventory $hat): self
    {
        $this->hat = $hat;

        return $this;
    }

    public function getCostume(): ?string
    {
        return $this->costume;
    }

    public function setCostume(string $costume): self
    {
        $this->costume = $costume;

        return $this;
    }

    public function getPetActivityStats(): ?PetActivityStats
    {
        return $this->petActivityStats;
    }

    public function setPetActivityStats(PetActivityStats $petActivityStats): self
    {
        $this->petActivityStats = $petActivityStats;

        // set the owning side of the relation if necessary
        if ($this !== $petActivityStats->getPet()) {
            $petActivityStats->setPet($this);
        }

        return $this;
    }

    /**
     * @return Collection|PetGroup[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(PetGroup $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->addMember($this);
        }

        return $this;
    }

    public function removeGroup(PetGroup $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            $group->removeMember($this);
        }

        return $this;
    }

    public function getExtroverted(): int
    {
        return $this->extroverted;
    }

    public function setExtroverted(int $extroverted): self
    {
        $this->extroverted = $extroverted;

        return $this;
    }

    public function getMaximumGroups(): int
    {
        if($this->hasMerit(MeritEnum::EXTROVERTED))
            return 4;

        if($this->extroverted <= -1)
            return 1;
        else if($this->extroverted === 0)
            return 2;
        else //if($this->extroverted >= 1)
            return 3;
    }

    public function getLoveLanguage(): ?string
    {
        return $this->loveLanguage;
    }

    public function setLoveLanguage(string $loveLanguage): self
    {
        $this->loveLanguage = $loveLanguage;

        return $this;
    }

    public function getIsGrandparent(): bool
    {
        return $this->isGrandparent;
    }

    public function setIsGrandparent(bool $isGrandparent): self
    {
        $this->isGrandparent = $isGrandparent;

        return $this;
    }

    public function getClaimedGrandparentMerit(): bool
    {
        return $this->claimedGrandparentMerit;
    }

    public function setClaimedGrandparentMerit(): self
    {
        $this->claimedGrandparentMerit = true;

        return $this;
    }

    public function getGuildMembership(): ?GuildMembership
    {
        return $this->guildMembership;
    }

    public function setGuildMembership(GuildMembership $guildMembership): self
    {
        $this->guildMembership = $guildMembership;

        // set the owning side of the relation if necessary
        if ($guildMembership->getPet() !== $this) {
            $guildMembership->setPet($this);
        }

        return $this;
    }

    public function getRevealedFavoriteFlavor(): int
    {
        return $this->revealedFavoriteFlavor;
    }

    public function setRevealedFavoriteFlavor(int $guesses): self
    {
        $this->revealedFavoriteFlavor = $guesses;

        return $this;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getFlavor(): string
    {
        if($this->revealedFavoriteFlavor > 0)
            return $this->favoriteFlavor;
        else
            return 'Unknown';
    }

    public function getAffectionAdventures(): ?int
    {
        return $this->affectionAdventures;
    }

    public function incrementAffectionAdventures(): self
    {
        $this->affectionAdventures++;

        return $this;
    }

    /**
     * @return Collection|LunchboxItem[]
     */
    public function getLunchboxItems(): Collection
    {
        return $this->lunchboxItems;
    }

    public function addLunchboxItem(LunchboxItem $lunchboxItem): self
    {
        if (!$this->lunchboxItems->contains($lunchboxItem)) {
            $this->lunchboxItems[] = $lunchboxItem;
            $lunchboxItem->setPet($this);
        }

        return $this;
    }

    public function removeLunchboxItem(LunchboxItem $lunchboxItem): self
    {
        if ($this->lunchboxItems->contains($lunchboxItem)) {
            $this->lunchboxItems->removeElement($lunchboxItem);
            // set the owning side to null (unless already changed)
            if ($lunchboxItem->getPet() === $this) {
                $lunchboxItem->setPet(null);
            }
        }

        return $this;
    }

    public function getSocialEnergy(): int
    {
        return $this->socialEnergy;
    }

    public function spendSocialEnergy(int $amount): self
    {
        $this->socialEnergy -= $amount;

        return $this;
    }
}
