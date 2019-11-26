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
        'music', 'smithing', 'computer'
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
    private $computer = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $gripX = 0.5;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $gripY = 0.5;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $gripAngle = 0;

    /**
     * A fixed grip angle means that the item will ALWAYS be rendered at this angle, regardless of the attributes of the pet that holds it
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $gripAngleFixed = false;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $gripScale = 1;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $focusSkill;

    /**
     * @ORM\Column(type="boolean")
     */
    private $providesLight;

    /**
     * @ORM\Column(type="boolean")
     */
    private $protectionFromHeat;

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

    public function getComputer(): ?int
    {
        return $this->computer;
    }

    public function setComputer(int $computer): self
    {
        $this->computer = $computer;

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
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    public function getModifiers(): array
    {
        $modifiers = [];

        foreach(self::MODIFIER_FIELDS as $modifier)
        {
            $value = $this->{'get' . $modifier}();

            if($value !== 0)
                $modifiers[] = self::rate($value) . ' ' . $modifier;
        }

        if($this->getProvidesLight())
            $modifiers[] = 'provides light';

        if($this->getProtectionFromHeat())
            $modifiers[] = 'protects from heat';

        if($this->getFocusSkill())
            $modifiers[] = 'learn faster when using ' . $this->getFocusSkill();

        return $modifiers;
    }


    public static function rate($value): ?string
    {
        if($value >= 10)
            return '++++';
        else if($value >= 6)
            return '+++';
        else if($value >= 3)
            return '++';
        else if($value >= 1)
            return '+';
        else if($value <= -10)
            return '----';
        else if($value <= -6)
            return '---';
        else if($value <= -3)
            return '--';
        else if($value <= -1)
            return '-';
        else
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
}
