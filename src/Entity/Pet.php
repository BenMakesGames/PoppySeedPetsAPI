<?php

namespace App\Entity;

use App\Enum\FlavorEnum;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\NumberFunctions;
use App\Service\PetRelationshipService;
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
 *     @ORM\Index(name="in_daycare_idx", columns={"in_daycare"}),
 * })
 */
class Pet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "myInventory", "parkEvent", "petFriend"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pets")
     * @Groups({"petPublicProfile", "parkEvent"})
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "myInventory", "petShelterPet", "parkEvent", "petFriend"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", name="`time`")
     */
    private $time = 60;

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
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend"})
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
     */
    private $skills;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PetSpecies")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend"})
     */
    private $species;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", inversedBy="pet")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile"})
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
     * @ORM\Column(type="json")
     * @Groups({"myPet"})
     */
    private $merits = [];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SpiritCompanion", inversedBy="pet", cascade={"persist", "remove"})
     * @Groups({"myPet", "parkEvent"})
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
    private $wouldBangFraction;

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
     */
    private $inDaycare = false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $curiosity;

    /**
     * @ORM\Column(type="smallint")
     */
    private $poly;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sexDrive;

    public function __construct()
    {
        $this->birthDate = new \DateTimeImmutable();
        $this->lastInteracted = (new \DateTimeImmutable())->modify('-3 days');
        $this->stomachSize = \mt_rand(16, 30);
        $this->petRelationships = new ArrayCollection();
        $this->statusEffects = new ArrayCollection();
        $this->curiosity = \mt_rand(-1, 1);

        // 10% poly; 10% flexible; 80% monogamous
        if(\mt_rand(1, 10) === 1)
            $this->poly = 1;
        else
            $this->poly = \mt_rand(1, 9) === 1 ? 0 : -1;

        // 2% asexual; 18% flexible; 80% sexual
        if(\mt_rand(1, 10) === 1)
            $this->sexDrive = -1;
        else
            $this->sexDrive = \mt_rand(1, 9) === 1 ? 0 : 1;
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
            return 'untouchable';
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

    public function getStomachSize(): int
    {
        return $this->stomachSize + ($this->hasMerit(MeritEnum::BLACK_HOLE_TUM) ? 6 : 0);
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
     * @Groups({"myPet"})
     */
    public function getLevel(): int
    {
        return $this->getSkills()->getTotal();
    }

    public function getExperienceToLevel(): int
    {
        return ($this->getLevel() + 1) * 10;
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
        return $this->getSkills()->getDexterity() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getDexterity() : 0);
    }

    public function getStrength(): int
    {
        return
            $this->getSkills()->getStrength() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getStrength() : 0) +
            ($this->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0)
        ;
    }

    public function getStamina(): int
    {
        return
            $this->getSkills()->getStamina() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getStamina() : 0) +
            ($this->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0)
        ;
    }

    public function getIntelligence(): int
    {
        return
            $this->getSkills()->getIntelligence() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getIntelligence() : 0) +
            ($this->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0)
        ;
    }

    public function getPerception(): int
    {
        return $this->getSkills()->getPerception() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getPerception() : 0);
    }

    public function getNature(): int
    {
        return $this->getSkills()->getNature() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getNature() : 0);
    }

    public function getBrawl(): int
    {
        return $this->getSkills()->getBrawl() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getBrawl() : 0);
    }

    public function getStealth(): int
    {
        return
            $this->getSkills()->getStealth() +
            ($this->getTool() ? $this->getTool()->getItem()->getTool()->getStealth() : 0) +
            ($this->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION) ? 1 : 0)
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

    public function getComputer(): int
    {
        return $this->getSkills()->getComputer() + ($this->getTool() ? $this->getTool()->getItem()->getTool()->getComputer() : 0);
    }

    public function getFavoriteFlavor(): string
    {
        return $this->favoriteFlavor;
    }

    public function setFavoriteFlavor(string $favoriteFlavor): self
    {
        if(!FlavorEnum::isAValue($favoriteFlavor))
            throw new \InvalidArgumentException('favoriteFlavor must be a value from FlavorEnum.');

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

    public function getMerits(): array
    {
        return $this->merits;
    }

    public function addMerit(string $merit): self
    {
        if(!MeritEnum::isAValue($merit))
            throw new \InvalidArgumentException('"' . $merit . '" is not a valid merit');

        if($this->hasMerit($merit))
            throw new \InvalidArgumentException($this->getName() . ' already has the merit "' . $merit . '"');

        $this->merits[] = $merit;

        return $this;
    }

    public function hasMerit(string $merit): bool
    {
        return in_array($merit, $this->getMerits());
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

    public function getOldWouldBang(Pet $otherPet): int
    {
        // a pet "would bang" another pet if:
        return
            // straight-up 1 in every 12-30 pets, averaging 1 in 24
            $this->getId() * 197 % ($this->wouldBangFraction * 3) === 0 ||
            (
                // 1 in every 2-5, averaging 1 in 4
                ($this->getId() * 127 + $otherPet->getId() * 31 - 157) % ceil($this->wouldBangFraction / 2) === 0 &&
                // AND 1 in every 2-5, averaging 1 in 4
                // (since this is an "and", the total odds are 1 in every 4-25, averaging 1 in 16)
                ($otherPet->getId() * 127 + $this->getId() * 31 - 157) % ceil($otherPet->getWouldBangFraction() / 2) === 0
            )
        ;
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
        if(!StatusEffectEnum::isAValue($statusEffect)) throw new \InvalidArgumentException('"' . $statusEffect . '" is not a known StatusEffectEnum value.');

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

    public function getInDaycare(): ?bool
    {
        return $this->inDaycare;
    }

    public function setInDaycare(bool $inDaycare): self
    {
        $this->inDaycare = $inDaycare;

        return $this;
    }

    public function getCuriosity(): ?int
    {
        return $this->curiosity;
    }

    public function setCuriosity(int $curiosity): self
    {
        $this->curiosity = $curiosity;

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

    public function getWouldBangFraction(): int
    {
        return $this->wouldBangFraction;
    }

    public function getSexDrive(): ?int
    {
        return $this->sexDrive;
    }

    public function setSexDrive(int $sexDrive): self
    {
        $this->sexDrive = $sexDrive;

        return $this;
    }
}
