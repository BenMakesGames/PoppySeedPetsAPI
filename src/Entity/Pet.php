<?php

namespace App\Entity;

use App\Enum\ActivityPersonalityEnum;
use App\Enum\AffectionExpressionEnum;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LoveLanguageEnum;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetPregnancyStyleEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\Squirrel3;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: PetRepository::class)]
#[ORM\Index(columns: ['park_event_type'], name: 'park_event_type_idx')]
#[ORM\Index(columns: ['park_event_order'], name: 'park_event_order_idx')]
#[ORM\Index(columns: ['location'], name: 'location_idx')]
#[ORM\Index(columns: ['name'], name: 'name_idx')]
#[ORM\Index(columns: ['last_interacted'], name: 'last_interacted_idx')]
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'myInventory', 'parkEvent', 'petFriend', 'hollowEarth', 'petGroupDetails', 'spiritCompanionPublicProfile', 'guildMember', 'petActivityLogAndPublicPet', 'helperPet'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pets')]
    #[Groups(['petPublicProfile', 'parkEvent'])]
    private $owner;

    #[ORM\Column(type: 'string', length: 40)]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'parkEvent', 'petFriend', 'hollowEarth', 'petGroupDetails', 'spiritCompanionPublicProfile', 'guildMember', 'petActivityLogAndPublicPet', 'helperPet'])]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $food = 0;

    #[ORM\Column(type: 'integer')]
    private $safety = 0;

    #[ORM\Column(type: 'integer')]
    private $love = 0;

    #[ORM\Column(type: 'integer')]
    private $esteem = 0;

    #[ORM\Column(type: 'integer')]
    private $experience = 0;

    #[ORM\Column(type: 'string', length: 6)]
    /**
     * uses custom serialization method, defined below
     */
    private $colorA;

    #[ORM\Column(type: 'string', length: 6)]
    /**
     * uses custom serialization method, defined below
     */
    private $colorB;

    #[ORM\Column(type: 'integer')]
    private $junk = 0;

    #[ORM\Column(type: 'integer')]
    private $alcohol = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'petPublicProfile'])]
    private $birthDate;

    #[ORM\Column(type: 'integer')]
    private $stomachSize;

    #[ORM\Column(type: 'datetime_immutable')]
    private $lastInteracted;

    #[ORM\OneToOne(inversedBy: 'pet', targetEntity: PetSkills::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $skills;

    #[ORM\ManyToOne(targetEntity: PetSpecies::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'parkEvent', 'petFriend', 'hollowEarth', 'petGroupDetails', 'guildMember', 'petActivityLogAndPublicPet', 'helperPet', 'petActivityLogs'])]
    private $species;

    #[ORM\OneToOne(targetEntity: Inventory::class, inversedBy: 'holder')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'hollowEarth', 'petGroupDetails', 'helperPet'])]
    private $tool;

    #[ORM\Column(type: 'string', length: 20)]
    private $favoriteFlavor;

    #[ORM\Column(type: 'text')]
    #[Groups([SerializationGroupEnum::MY_PET])]
    private $note = '';

    #[ORM\Column(type: 'integer')]
    private $affectionPoints = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet'])]
    private $affectionLevel = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet'])]
    private $affectionRewardsClaimed = 0;

    #[ORM\OneToOne(targetEntity: SpiritCompanion::class, inversedBy: 'pet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'parkEvent', 'hollowEarth', 'petPublicProfile', 'petGroupDetails', 'helperPet'])]
    private $spiritCompanion;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups([SerializationGroupEnum::MY_PET])]
    private $lastParkEvent;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    #[Groups([SerializationGroupEnum::MY_PET])]
    private $parkEventType;

    #[ORM\Column(type: 'integer')]
    private int $parkEventOrder = 0;

    #[ORM\OneToMany(mappedBy: 'pet', targetEntity: PetRelationship::class, orphanRemoval: true)]
    private $petRelationships;

    #[ORM\Column(type: 'integer')]
    private $caffeine = 0;

    #[ORM\Column(type: 'integer')]
    private $psychedelic = 0;

    #[ORM\Column(type: 'integer')]
    private $poison = 0;

    #[ORM\OneToMany(targetEntity: StatusEffect::class, mappedBy: 'pet', orphanRemoval: true)]
    private $statusEffects;

    #[ORM\Column(type: 'smallint')]
    private $sexDrive;

    #[ORM\OneToOne(targetEntity: PetBaby::class, inversedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'petFriend', 'petGroupDetails', 'helperPet'])]
    private $pregnancy;

    #[ORM\ManyToOne(inversedBy: 'motheredPets', targetEntity: Pet::class)]
    private $mom;

    #[ORM\OneToMany(mappedBy: 'mom', targetEntity: Pet::class)]
    private $motheredPets;

    #[ORM\ManyToOne(inversedBy: 'fatheredPets', targetEntity: Pet::class)]
    private $dad;

    #[ORM\OneToMany(mappedBy: 'dad', targetEntity: Pet::class)]
    private $fatheredPets;

    #[ORM\ManyToOne(targetEntity: SpiritCompanion::class, inversedBy: 'fatheredPets')]
    private $spiritDad;

    #[ORM\ManyToMany(targetEntity: Merit::class)]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'petGroupDetails', 'parkEvent', 'petFriend', 'hollowEarth', 'petActivityLogAndPublicPet', 'helperPet'])]
    private $merits;

    #[ORM\Column(type: 'boolean')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet'])]
    private $isFertile = false;

    #[ORM\OneToOne(targetEntity: Inventory::class, inversedBy: 'wearer')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'hollowEarth', 'petGroupDetails', 'helperPet'])]
    private $hat;

    #[ORM\Column(type: 'string', length: 30)]
    #[Groups([SerializationGroupEnum::MY_PET, 'houseSitterPet', 'petPublicProfile'])]
    private $costume = '';

    #[ORM\OneToOne(mappedBy: 'pet', targetEntity: PetActivityStats::class, cascade: ['persist', 'remove'])]
    private $petActivityStats;

    #[ORM\ManyToMany(targetEntity: PetGroup::class, mappedBy: 'members')]
    #[Groups(['petPublicProfile'])]
    private $groups;

    #[ORM\Column(type: 'smallint')]
    private $extroverted;

    #[ORM\Column(type: 'string', length: 10)]
    private $loveLanguage;

    #[ORM\Column(type: 'boolean')]
    private $isGrandparent = false;

    #[ORM\Column(type: 'boolean')]
    private $claimedGrandparentMerit = false;

    #[ORM\OneToOne(mappedBy: 'pet', targetEntity: GuildMembership::class, cascade: ['persist', 'remove'])]
    #[Groups(['petPublicProfile', 'guildMember'])]
    private $guildMembership;

    #[ORM\Column(type: 'integer')]
    private $revealedFavoriteFlavor = 0;

    #[ORM\Column(type: 'integer')]
    private $affectionAdventures = 0;

    #[ORM\OneToMany(mappedBy: 'pet', targetEntity: LunchboxItem::class)]
    #[Groups(['myPet', 'houseSitterPet'])]
    private $lunchboxItems;

    #[ORM\Column(type: 'smallint')]
    private $bonusMaximumFriends;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['myPet', 'houseSitterPet'])]
    private $selfReflectionPoint = 0;

    #[ORM\OneToOne(targetEntity: PetHouseTime::class, mappedBy: 'pet', cascade: ['persist', 'remove'])]
    #[Groups(['myPet', 'houseSitterPet'])]
    private $houseTime;

    /**
     * uses custom serialization method, defined below
     */
    #[ORM\Column(type: 'smallint')]
    private $scale = 100;

    #[ORM\OneToOne(targetEntity: PetCraving::class, mappedBy: 'pet', cascade: ['persist', 'remove'])]
    private $craving;

    #[ORM\Column(type: 'integer')]
    private $activityPersonality;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['myPetLocation'])]
    private $location = PetLocationEnum::HOME;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $locationMoveDate;

    #[ORM\Column(type: 'string', length: 3)]
    private $affectionExpressions;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['myPet'])]
    private $renamingCharges = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups(['myPet', 'houseSitterPet'])]
    private $lunchboxIndex;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['myPet', 'houseSitterPet', 'petActivityLogs'])]
    private $wereform;

    public function __construct()
    {
        $squirrel3 = new Squirrel3();

        $this->birthDate = new \DateTimeImmutable();
        $this->lastInteracted = (new \DateTimeImmutable())->modify('-3 days');
        $this->locationMoveDate = new \DateTimeImmutable();
        $this->stomachSize = $squirrel3->rngNextInt(16, 30);
        $this->petRelationships = new ArrayCollection();
        $this->statusEffects = new ArrayCollection();
        $this->extroverted = $squirrel3->rngNextInt(-1, 1);
        $this->bonusMaximumFriends = $squirrel3->rngNextInt(-2, 2);
        $this->wereform = $squirrel3->rngNextInt(0, 5);

        if($squirrel3->rngNextInt(1, 5) > 1)
            $this->sexDrive = 1; // 80% sexual
        else if($squirrel3->rngNextInt(1, 10) === 1)
            $this->sexDrive = -1; // 2% asexual
        else
            $this->sexDrive = 0; // 18% flexible

        $this->motheredPets = new ArrayCollection();
        $this->fatheredPets = new ArrayCollection();
        $this->merits = new ArrayCollection();
        $this->groups = new ArrayCollection();

        $this->loveLanguage = LoveLanguageEnum::getRandomValue($squirrel3);
        $this->lunchboxItems = new ArrayCollection();

        $this->affectionExpressions = join('', $squirrel3->rngNextSubsetFromArray(AffectionExpressionEnum::getValues(), 3));

        $this->assignActivityPersonality($squirrel3);

        $this->lunchboxIndex = $squirrel3->rngNextInt(0, 13);
    }

    public function assignActivityPersonality(IRandom $squirrel3)
    {
        $activityPersonalities = $squirrel3->rngNextSubsetFromArray(ActivityPersonalityEnum::getValues(), 3);
        $this->activityPersonality = $activityPersonalities[0] | $activityPersonalities[1] | $activityPersonalities[2];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        foreach($this->getLunchboxItems() as $lunchboxItem)
        {
            $lunchboxItem->getInventoryItem()
                ->forceSetOwner($owner)
            ;
        }

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

    public function resetAllNeeds(): self
    {
        $this->food = 0;
        $this->safety = 0;
        $this->love = 0;
        $this->esteem = 0;

        return $this;
    }

    public function setFoodAndSafety(int $food, int $safety): self
    {
        $this->food = $food;
        $this->safety = $safety;

        return $this;
    }

    public function getFood(): int
    {
        return $this->food;
    }

    public function getMinFood()
    {
        return -16;
    }

    public function increaseFood(int $amount, ?int $max = null): self
    {
        if($amount === 0) return $this;
        if($max && $amount > 0 && $this->food >= $max) return $this;

        $this->food = NumberFunctions::clamp(
            $this->food + $amount,
            $this->getMinFood(),                                    // minimum
            $max ?? $this->getStomachSize() - max(0, $this->junk)   // maximum
        );

        return $this;
    }

    public function getSafety(): int
    {
        return $this->safety;
    }

    public function increaseSafety(int $amount, ?int $max = null): self
    {
        if($amount === 0) return $this;
        if($max && $amount > 0 && $this->safety >= $max) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount == 0) return $this;

        $this->safety = NumberFunctions::clamp($this->safety + $amount, $this->getMinSafety(), $max ?? $this->getMaxSafety());

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

    public function increaseLove(int $amount, ?int $max = null): self
    {
        if($amount === 0) return $this;
        if($max && $amount > 0 && $this->love >= $max) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;
        if($this->getSafety() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount == 0) return $this;

        $this->love = NumberFunctions::clamp($this->love + $amount, $this->getMinLove(), $max ?? $this->getMaxLove());

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

    public function increaseEsteem(int $amount, ?int $max = null): self
    {
        if($amount === 0) return $this;
        if($max && $amount > 0 && $this->esteem >= $max) return $this;

        $divisor = 1;

        if($this->getFood() + $this->getAlcohol() < 0) $divisor++;
        if($this->getSafety() + $this->getAlcohol() < 0) $divisor++;
        if($this->getLove() + $this->getAlcohol() < 0) $divisor++;

        $amount = floor($amount / $divisor);

        if($amount == 0) return $this;

        $this->esteem = NumberFunctions::clamp($this->esteem + $amount, $this->getMinEsteem(), $max ?? $this->getMaxEsteem());

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

    public function clearExp(): self
    {
        $this->experience = 0;
        return $this;
    }

    #[SerializedName('colorA')]
    #[Groups(['myPet', 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'parkEvent', 'petFriend', 'hollowEarth', 'petGroupDetails', 'guildMember', 'petActivityLogAndPublicPet', 'helperPet', 'petActivityLogs', 'petActivityLogs'])]
    public function getPerceivedColorA(): string
    {
        if($this->hasStatusEffect(StatusEffectEnum::INVISIBLE))
            return '';
        else if($this->hasStatusEffect(StatusEffectEnum::EGGPLANT_CURSED))
            return '673192';
        else if(($this->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE) && !$this->hasMerit(MeritEnum::BLUSH_OF_LIFE)) || ($this->getTool() && $this->getTool()->isGrayscaling()))
            return ColorFunctions::GrayscalifyHex($this->getColorA());
        else if($this->getTool() && $this->getTool()->isGreenifying())
            return ColorFunctions::ChangeHue($this->getColorA(), (25 + $this->id % 14) / 100);
        else
            return $this->getColorA();
    }

    #[SerializedName('colorB')]
    #[Groups(['myPet', 'houseSitterPet', 'userPublicProfile', 'petPublicProfile', 'parkEvent', 'petFriend', 'hollowEarth', 'petGroupDetails', 'guildMember', 'petActivityLogAndPublicPet', 'helperPet', 'petActivityLogs'])]
    public function getPerceivedColorB(): string
    {
        if($this->hasStatusEffect(StatusEffectEnum::INVISIBLE))
            return '';
        else if($this->hasStatusEffect(StatusEffectEnum::EGGPLANT_CURSED))
            return '8b48c1';
        else if(($this->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE) && !$this->hasMerit(MeritEnum::BLUSH_OF_LIFE)) || ($this->getTool() && $this->getTool()->isGrayscaling()))
            return ColorFunctions::GrayscalifyHex($this->getColorB());
        else if($this->getTool() && $this->getTool()->isGreenifying())
            return ColorFunctions::ChangeHue($this->getColorB(), (25 + $this->id % 14) / 100);
        else
            return $this->getColorB();
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
        $this->junk = NumberFunctions::clamp($this->junk + $amount, 0, $this->getStomachSize() - max(0, $this->food));

        return $this;
    }

    public function getAlcohol(): int
    {
        return $this->alcohol;
    }

    public function increaseAlcohol(int $amount): self
    {
        if($amount === 0) return $this;

        $this->alcohol = NumberFunctions::clamp($this->alcohol + $amount, 0, 16);

        return $this;
    }

    public function increaseCaffeine(int $amount): self
    {
        if($amount === 0) return $this;

        $this->caffeine = NumberFunctions::clamp($this->caffeine + $amount, 0, 16);

        return $this;
    }

    public function increasePsychedelic(int $amount): self
    {
        if($amount === 0) return $this;

        $this->psychedelic = NumberFunctions::clamp($this->psychedelic + $amount, 0, $this->getMaxPsychedelic());

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

    public function getFullnessPercent(): float
    {
        if($this->getFood() + $this->getJunk() >= 0)
            return ($this->getFood() + $this->getJunk()) / $this->getStomachSize();
        else
            return ($this->getFood() + $this->getJunk()) / -$this->getMinFood();
    }

    public function getSafetyPercent(): float
    {
        if($this->getSafety() >= 0)
            return $this->getSafety() / $this->getMaxSafety();
        else
            return $this->getSafety() / -$this->getMinSafety();
    }

    public function getLovePercent(): float
    {
        if($this->getLove() >= 0)
            return $this->getLove() / $this->getMaxLove();
        else
            return $this->getLove() / -$this->getMinLove();
    }

    public function getEsteemPercent(): float
    {
        if($this->getEsteem() >= 0)
            return $this->getEsteem() / $this->getMaxEsteem();
        else
            return $this->getEsteem() / -$this->getMinEsteem();
    }

    #[Groups(['myPet', 'houseSitterPet'])]
    public function getNeeds()
    {
        $needs = [
            'food' => [
                'description' => $this->getFull(),
            ],
            'safety' => [
                'description' => $this->getSafe(),
            ],
            'love' => [
                'description' => $this->getLoved(),
            ],
            'esteem' => [
                'description' => $this->getEsteemed(),
            ],
        ];

        if($this->hasStatusEffect(StatusEffectEnum::X_RAYD))
        {
            $needs['food']['percent'] = round($this->getFullnessPercent(), 2);
            $needs['safety']['percent'] = round($this->getSafetyPercent(), 2);
            $needs['love']['percent'] = round($this->getLovePercent(), 2);
            $needs['esteem']['percent'] = round($this->getEsteemPercent(), 2);
        }

        return $needs;
    }

    public function getFull(): string
    {
        $fullness = $this->getFullnessPercent();

        if($fullness >= 0.75)
        {
            if($this->getSpecies()->getFamily() === 'fish')
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

    public function getSafe(): string
    {
        if($this->getSafety() >= 16)
            return 'invincible';
        else if($this->getSafety() >= 8) // 8 to 15
            return 'safe';
        else if($this->getSafety() >= 0) // 0 to 7
            return '...';
        else if($this->getSafety() >= -12) // -12 to -1
            return 'on edge';
        else
            return 'terrified';
    }

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

    #[Groups(['myPet', 'houseSitterPet'])]
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

    #[Groups(['myPet', 'houseSitterPet'])]
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

    #[Groups(['myPet', 'houseSitterPet'])]
    public function getPoisonLevel(): string
    {
        if($this->getPoison() > 16)
            return 'major-vommage!';
        else if($this->getPoison() > 12)
            return 'very';
        else if($this->getPoison() > 8)
            return 'somewhat';
        else if($this->getPoison() > 4)
            return 'a little';
        else if($this->getPoison() > 0)
            return 'a touch';
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

    #[Groups(['myPet', 'houseSitterPet'])]
    public function getCanInteract(): bool
    {
        return $this->getLastInteracted() < (new \DateTimeImmutable())->modify('-4 hours');
    }

    #[Groups(['myPet'])]
    public function getCanParticipateInParkEvents(): bool
    {
        return $this->getLastInteracted() > (new \DateTimeImmutable())->modify('-48 hours');
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

    #[Groups(['myPet', 'houseSitterPet', 'petPublicProfile'])]
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

    public function increaseAffectionLevel(int $amount = 1): self
    {
        $this->affectionLevel += $amount;

        return $this;
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
        $this->parkEventOrder = random_int(0, 2000000000);

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

    #[Groups(['myPet', 'houseSitterPet'])]
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
        return ArrayFunctions::find_one($this->getPetRelationships(), fn(PetRelationship $r) =>
            $r->getRelationship()->getId() === $otherPet->getId()
        );
    }

    public function getRelationshipCount(): int
    {
        return count($this->getPetRelationships());
    }

    public function getLowestNeed(): string
    {
        $squirrel3 = new Squirrel3();

        if($this->getSafety() >= $squirrel3->rngNextInt(0, 4) && $this->getLove() >= $squirrel3->rngNextInt(0, 4) && $this->getEsteem() >= $squirrel3->rngNextInt(0, 4))
        {
            return '';
        }
        else if($this->getSafety() <= $this->getLove() + $squirrel3->rngNextInt(0, 4) && $this->getSafety() <= $this->getEsteem() + $squirrel3->rngNextInt(0, 4))
        {
            return 'safety';
        }
        else if($this->getSafety() <= $this->getEsteem() + $squirrel3->rngNextInt(2, 4))
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
        $this->poison = NumberFunctions::clamp($this->poison + $poison, 0, 24);

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

        return ArrayFunctions::find_one($this->statusEffects, fn(StatusEffect $se) =>
            $se->getStatus() === $statusEffect
        );
    }

    public function hasStatusEffect(string $statusEffect): bool
    {
        return $this->getStatusEffect($statusEffect) !== null;
    }

    #[Groups(['myPet', 'houseSitterPet', 'petActivityLogs'])]
    public function getStatuses(): array
    {
        return array_values(array_map(fn(StatusEffect $se) => $se->getStatus(), $this->statusEffects->toArray()));
    }

    #[Groups(['myPet', 'houseSitterPet'])]
    public function getCanPickTalent(): ?string
    {
        if($this->getSkills()->getTalent() === null)
        {
            if($this->getHouseTime()->getTimeSpent() >= 15000) // 15000 = ~10.4 days of minutes
                return 'talent';
        }
        else if($this->getSkills()->getExpertise() === null)
        {
            if($this->getHouseTime()->getTimeSpent() >= 150000) // 150000 = 104 days of minutes
                return 'expertise';
        }

        return null;
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

    #[Groups(['petPublicProfile', 'myPet', 'houseSitterPet'])]
    public function getMaximumFriends(): int
    {
        if($this->extroverted <= -1)
            return 10 + $this->getBonusMaximumFriends();
        else if($this->extroverted === 0)
            return 15 + $this->getBonusMaximumFriends();
        else //if($this->extroverted >= 1)
            return 20 + $this->getBonusMaximumFriends();
    }

    public function getMaximumGroups(): int
    {
        if($this->hasMerit(MeritEnum::GREGARIOUS))
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

    public function isInGuild(string $guildName, int $minTitle = 1): bool
    {
        return
            $this->getGuildMembership() &&
            $this->getGuildMembership()->getGuild()->getName() === $guildName &&
            $this->getGuildMembership()->getTitle() >= $minTitle
        ;
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

    #[Groups(['myPet', 'houseSitterPet'])]
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

    public function getLunchboxSize(): int
    {
        return $this->hasMerit(MeritEnum::BIGGER_LUNCHBOX) ? 5 : 4;
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

    public function getBonusMaximumFriends(): ?int
    {
        return $this->bonusMaximumFriends;
    }

    public function setBonusMaximumFriends(int $bonusMaximumFriends): self
    {
        $this->bonusMaximumFriends = $bonusMaximumFriends;

        return $this;
    }

    public function getSelfReflectionPoint(): ?int
    {
        return $this->selfReflectionPoint;
    }

    public function increaseSelfReflectionPoint(int $amount): self
    {
        $this->selfReflectionPoint += $amount;

        return $this;
    }

    public function getHouseTime(): ?PetHouseTime
    {
        return $this->houseTime;
    }

    public function setHouseTime(PetHouseTime $houseTime): self
    {
        $this->houseTime = $houseTime;

        // set the owning side of the relation if necessary
        if ($houseTime->getPet() !== $this) {
            $houseTime->setPet($this);
        }

        return $this;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(int $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

    #[SerializedName('scale')]
    #[Groups(["myPet", 'houseSitterPet', "userPublicProfile", "petPublicProfile", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails", "guildMember", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    public function getPerceivedScale(): int
    {
        if(!$this->getMom())
            return $this->getScale();

        $factor = min(14, (new \DateTimeImmutable())->diff($this->getBirthDate())->days) / 14 * 0.5 + 0.5;

        return round($this->getScale() * $factor);
    }

    #[SerializedName('skills')]
    #[Groups(["myPet", 'houseSitterPet'])]
    public function getComputedSkills(): ComputedPetSkills
    {
        return new ComputedPetSkills($this);
    }

    public function getCraving(): ?PetCraving
    {
        return $this->craving;
    }

    public function setCraving(?PetCraving $craving): self
    {
        // set the owning side of the relation if necessary
        if ($craving && $craving->getPet() !== $this) {
            $craving->setPet($this);
        }

        $this->craving = $craving;

        return $this;
    }

    public function hasCraving(): bool
    {
        return $this->craving !== null;
    }

    #[SerializedName('craving')]
    #[Groups(["myPet", 'houseSitterPet'])]
    public function getSerializedCraving(): ?string
    {
        if(!$this->getCraving() || $this->getCraving()->isSatisfied())
            return null;

        return $this->getCraving()->getFoodGroup()->getName();
    }

    public function hasActivityPersonality(int $personality): bool
    {
        return ($this->activityPersonality & $personality) === $personality;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        if(!PetLocationEnum::isAValue($location))
            throw new EnumInvalidValueException(PetLocationEnum::class, $location);

        $this->location = $location;
        $this->locationMoveDate = new \DateTimeImmutable();

        return $this;
    }

    public function getLocationMoveDate(): \DateTimeImmutable
    {
        return $this->locationMoveDate;
    }

    public function isAtHome(): bool
    {
        return $this->location === PetLocationEnum::HOME;
    }

    public function getRandomAffectionExpression(IRandom $rng): ?string
    {
        if($this->hasMerit(MeritEnum::AFFECTIONLESS))
            return null;

        return \mb_substr($this->affectionExpressions, $rng->rngNextInt(0, \mb_strlen($this->affectionExpressions) - 1), 1);
    }

    public function setAffectionExpressions(string $affectionExpressions): self
    {
        $this->affectionExpressions = $affectionExpressions;

        return $this;
    }

    public function getRenamingCharges(): int
    {
        return $this->renamingCharges;
    }

    public function setRenamingCharges(int $renamingCharges): self
    {
        $this->renamingCharges = $renamingCharges;

        return $this;
    }

    public function getSpiritDad(): ?SpiritCompanion
    {
        return $this->spiritDad;
    }

    public function setSpiritDad(?SpiritCompanion $spiritDad): self
    {
        $this->spiritDad = $spiritDad;

        return $this;
    }

    public function getLunchboxIndex(): ?int
    {
        return $this->lunchboxIndex;
    }

    public function setLunchboxIndex(int $lunchboxIndex): self
    {
        $this->lunchboxIndex = $lunchboxIndex;

        return $this;
    }

    public function getWereform(): ?int
    {
        return $this->wereform;
    }

    public function setWereform(int $wereform): self
    {
        $this->wereform = $wereform;

        return $this;
    }

    private const MeritsApplicableToReenactments = [
        MeritEnum::INVERTED,
        MeritEnum::VERY_INVERTED,
        MeritEnum::SPECTRAL,
    ];

    #[Groups(['petActivityLogs'])]
    #[SerializedName('merits')]
    public function getPetActivityLogMerits(): array
    {
        return array_values(
            $this->merits
                ->filter(fn(Merit $m) => in_array($m->getName(), self::MeritsApplicableToReenactments))
                ->map(fn(Merit $m) => [
                    'name' => $m->getName(),
                ])
                ->toArray()
        );
    }
}
