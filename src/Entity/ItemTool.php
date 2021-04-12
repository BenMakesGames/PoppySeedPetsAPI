<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemToolRepository")
 */
class ItemTool
{
    public const MODIFIER_FIELDS = [
        'stealth', 'nature', 'brawl', 'umbra', 'crafts', 'fishing', 'gathering',
        'music', 'smithing', 'science', 'climbing'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $stealth = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $nature = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $brawl = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $umbra = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $crafts = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fishing = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $gathering = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $music = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $smithing = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $science = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $gripX = 0.5;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $gripY = 0.5;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $gripAngle = 0;

    /**
     * A fixed grip angle means that the item will ALWAYS be rendered at this angle, regardless of the attributes of the pet that holds it
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $gripAngleFixed = false;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $gripScale = 1;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $focusSkill;

    /**
     * @ORM\Column(type="boolean")
     */
    private $providesLight = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $protectionFromHeat = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    private $alwaysInFront = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRanged = false;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     */
    private $whenGather;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     */
    private $whenGatherAlsoGather;

    /**
     * @ORM\Column(type="integer")
     */
    private $climbing = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $leadsToAdventure = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $preventsBugs = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attractsBugs = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Enchantment", mappedBy="effects", cascade={"persist", "remove"})
     */
    private $enchantment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canBeNibbled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $increasesPooping = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dreamcatcher = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isGrayscaling = false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $socialEnergyModifier = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sexDrive = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $whenGatherPreventGather = false;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $adventureDescription;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStealth(): int
    {
        return $this->stealth;
    }

    public function setStealth(int $stealth): self
    {
        $this->stealth = $stealth;

        return $this;
    }

    public function getNature(): int
    {
        return $this->nature;
    }

    public function setNature(int $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    public function getBrawl(): int
    {
        return $this->brawl;
    }

    public function setBrawl(int $brawl): self
    {
        $this->brawl = $brawl;

        return $this;
    }

    public function getUmbra(): int
    {
        return $this->umbra;
    }

    public function setUmbra(int $umbra): self
    {
        $this->umbra = $umbra;

        return $this;
    }

    public function getCrafts(): int
    {
        return $this->crafts;
    }

    public function setCrafts(int $crafts): self
    {
        $this->crafts = $crafts;

        return $this;
    }

    public function getFishing(): int
    {
        return $this->fishing;
    }

    public function setFishing(int $fishing): self
    {
        $this->fishing = $fishing;

        return $this;
    }

    public function getGathering(): int
    {
        return $this->gathering;
    }

    public function setGathering(int $gathering): self
    {
        $this->gathering = $gathering;

        return $this;
    }

    public function getMusic(): int
    {
        return $this->music;
    }

    public function setMusic(int $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getSmithing(): int
    {
        return $this->smithing;
    }

    public function setSmithing(int $smithing): self
    {
        $this->smithing = $smithing;

        return $this;
    }

    public function getScience(): ?int
    {
        return $this->science;
    }

    public function setScience(int $science): self
    {
        $this->science = $science;

        return $this;
    }

    public function getGripX(): float
    {
        return $this->gripX;
    }

    public function setGripX(float $gripX): self
    {
        $this->gripX = $gripX;

        return $this;
    }

    public function getGripY(): float
    {
        return $this->gripY;
    }

    public function setGripY(float $gripY): self
    {
        $this->gripY = $gripY;

        return $this;
    }

    public function getGripAngle(): int
    {
        return $this->gripAngle;
    }

    public function setGripAngle(int $gripAngle): self
    {
        $this->gripAngle = $gripAngle;

        return $this;
    }

    public function getGripAngleFixed(): bool
    {
        return $this->gripAngleFixed;
    }

    public function setGripAngleFixed(bool $gripAngleFixed): self
    {
        $this->gripAngleFixed = $gripAngleFixed;

        return $this;
    }

    public function getGripScale(): float
    {
        return $this->gripScale;
    }

    public function setGripScale(float $gripScale): self
    {
        $this->gripScale = $gripScale;

        return $this;
    }

    /**
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem"})
     */
    public function getModifiers(): array
    {
        $modifiers = [];

        foreach(self::MODIFIER_FIELDS as $modifier)
        {
            $value = $this->{'get' . $modifier}();

            if($value !== 0)
                $modifiers[] = ($value > 0 ? '+' : '') . $value . ' ' . $modifier;
        }

        if($this->getIsRanged())
            $modifiers[] = 'is only useful at a distance';

        if($this->getLeadsToAdventure())
        {
            if($this->getAdventureDescription())
                $modifiers[] = 'leads to adventure: ' . $this->getAdventureDescription();
            else if($this->getId() === 205) // aubergine commander
                $modifiers[] = 'leads to... adventure??';
            else
                $modifiers[] = 'leads to adventure!';
        }

        if($this->getProvidesLight())
            $modifiers[] = 'provides light';

        if($this->getProtectionFromHeat())
            $modifiers[] = 'protects from heat';

        if($this->getFocusSkill())
            $modifiers[] = 'learn faster when using ' . $this->getFocusSkill();

        if($this->getWhenGather())
        {
            if($this->getWhenGatherAlsoGather())
            {
                if($this->getWhenGatherPreventGather())
                    $modifiers[] = 'when the pet would obtain ' . $this->getWhenGather()->getName() . ', it gets ' . $this->getWhenGatherAlsoGather()->getName() . ' instead!';
                else if($this->getWhenGather()->getId() === $this->getWhenGatherAlsoGather()->getId())
                    $modifiers[] = 'when the pet obtains ' . $this->getWhenGather()->getName() . ', it gets another ' . $this->getWhenGatherAlsoGather()->getName();
                else
                    $modifiers[] = 'when the pet obtains ' . $this->getWhenGather()->getName() . ', it also gets ' . $this->getWhenGatherAlsoGather()->getName();
            }
            else if($this->getWhenGatherPreventGather())
                $modifiers[] = 'the pet can never obtain ' . $this->getWhenGather()->getName() . '!';
        }

        if($this->getAttractsBugs())
            $modifiers[] = 'when the pet obtains any bug, it gets another of the same bug';

        if($this->getPreventsBugs())
            $modifiers[] = 'prevents bugs from following the pet home';

        if($this->getCanBeNibbled())
            $modifiers[] = 'the pet will occasionally nibble on this item';

        if($this->getIncreasesPooping())
            $modifiers[] = 'the pet will occasionally... find Dark Matter';

        if($this->getDreamcatcher())
            $modifiers[] = 'the pet will occasionally have vivid dreams';

        if($this->getIsGrayscaling())
            $modifiers[] = 'the pet will be in black & white';

        if($this->getSocialEnergyModifier() != 0)
            $modifiers[] = 'the pet will hang out with others ' . $this->describeSocialEnergyModifier() . ' often';

        if($this->getSexDrive() > 0)
            $modifiers[] = 'the pet will "have fun ;)" with partners more often';
        else if($this->getSexDrive() < 0)
            $modifiers[] = 'the pet will "have fun ;)" with partners less often';

        return $modifiers;
    }

    public function describeSocialEnergyModifier(): ?string
    {
        if($this->socialEnergyModifier < -30)
            return 'WAY more';
        else if($this->socialEnergyModifier < -15)
            return 'more';
        else if($this->socialEnergyModifier < 0)
            return 'a little more';
        else if($this->socialEnergyModifier > 30)
            return 'WAY less';
        else if($this->socialEnergyModifier > 15)
            return 'less';
        else if($this->socialEnergyModifier > 0)
            return 'a little less';

        return null;
    }

    public function getFocusSkill(): ?string
    {
        return $this->focusSkill;
    }

    public function setFocusSkill(?string $focusSkill): self
    {
        $this->focusSkill = $focusSkill;

        return $this;
    }

    public function getProvidesLight(): ?bool
    {
        return $this->providesLight;
    }

    public function setProvidesLight(bool $providesLight): self
    {
        $this->providesLight = $providesLight;

        return $this;
    }

    public function getProtectionFromHeat(): ?bool
    {
        return $this->protectionFromHeat;
    }

    public function setProtectionFromHeat(bool $protectionFromHeat): self
    {
        $this->protectionFromHeat = $protectionFromHeat;

        return $this;
    }

    public function getAlwaysInFront(): ?bool
    {
        return $this->alwaysInFront;
    }

    public function setAlwaysInFront(bool $alwaysInFront): self
    {
        $this->alwaysInFront = $alwaysInFront;

        return $this;
    }

    public function getIsRanged(): ?bool
    {
        return $this->isRanged;
    }

    public function setIsRanged(bool $isRanged): self
    {
        $this->isRanged = $isRanged;

        return $this;
    }

    public function getWhenGather(): ?Item
    {
        return $this->whenGather;
    }

    public function setWhenGather(?Item $whenGather): self
    {
        $this->whenGather = $whenGather;

        return $this;
    }

    public function getWhenGatherAlsoGather(): ?Item
    {
        return $this->whenGatherAlsoGather;
    }

    public function setWhenGatherAlsoGather(?Item $whenGatherAlsoGather): self
    {
        $this->whenGatherAlsoGather = $whenGatherAlsoGather;

        return $this;
    }

    public function getClimbing(): int
    {
        return $this->climbing;
    }

    public function setClimbing(int $climbing): self
    {
        $this->climbing = $climbing;

        return $this;
    }

    public function getLeadsToAdventure(): bool
    {
        return $this->leadsToAdventure;
    }

    public function setLeadsToAdventure(bool $leadsToAdventure): self
    {
        $this->leadsToAdventure = $leadsToAdventure;

        return $this;
    }

    public function getPreventsBugs(): ?bool
    {
        return $this->preventsBugs;
    }

    public function setPreventsBugs(bool $preventsBugs): self
    {
        $this->preventsBugs = $preventsBugs;

        return $this;
    }

    public function getAttractsBugs(): ?bool
    {
        return $this->attractsBugs;
    }

    public function setAttractsBugs(bool $attractsBugs): self
    {
        $this->attractsBugs = $attractsBugs;

        return $this;
    }

    public function getEnchantment(): ?Enchantment
    {
        return $this->enchantment;
    }

    public function setEnchantment(Enchantment $enchantment): self
    {
        $this->enchantment = $enchantment;

        // set the owning side of the relation if necessary
        if ($enchantment->getEffects() !== $this) {
            $enchantment->setEffects($this);
        }

        return $this;
    }

    public function getCanBeNibbled(): ?bool
    {
        return $this->canBeNibbled;
    }

    public function setCanBeNibbled(bool $canBeNibbled): self
    {
        $this->canBeNibbled = $canBeNibbled;

        return $this;
    }

    public function getIncreasesPooping(): ?bool
    {
        return $this->increasesPooping;
    }

    public function setIncreasesPooping(bool $increasesPooping): self
    {
        $this->increasesPooping = $increasesPooping;

        return $this;
    }

    public function getDreamcatcher(): ?bool
    {
        return $this->dreamcatcher;
    }

    public function setDreamcatcher(bool $dreamcatcher): self
    {
        $this->dreamcatcher = $dreamcatcher;

        return $this;
    }

    public function getIsGrayscaling(): ?bool
    {
        return $this->isGrayscaling;
    }

    public function setIsGrayscaling(bool $isGrayscaling): self
    {
        $this->isGrayscaling = $isGrayscaling;

        return $this;
    }

    public function getSocialEnergyModifier(): int
    {
        return $this->socialEnergyModifier;
    }

    public function setSocialEnergyModifier(int $socialEnergyModifier): self
    {
        $this->socialEnergyModifier = $socialEnergyModifier;

        return $this;
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

    public function getWhenGatherPreventGather(): ?bool
    {
        return $this->whenGatherPreventGather;
    }

    public function setWhenGatherPreventGather(bool $whenGatherPreventGather): self
    {
        $this->whenGatherPreventGather = $whenGatherPreventGather;

        return $this;
    }

    public function getAdventureDescription(): ?string
    {
        return $this->adventureDescription;
    }

    public function setAdventureDescription(?string $adventureDescription): self
    {
        $this->adventureDescription = $adventureDescription;

        return $this;
    }
}
