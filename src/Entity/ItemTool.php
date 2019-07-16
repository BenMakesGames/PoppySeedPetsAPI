<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemToolRepository")
 */
class ItemTool
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $strength = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $dexterity = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $intelligence = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $perception = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $stealth = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $stamina = 0;

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
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripY;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripAngle;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripScale;

    /**
     * @ORM\Column(type="integer")
     */
    private $music = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $smithing = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;

        return $this;
    }

    public function getDexterity(): int
    {
        return $this->dexterity;
    }

    public function setDexterity(int $dexterity): self
    {
        $this->dexterity = $dexterity;

        return $this;
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): self
    {
        $this->intelligence = $intelligence;

        return $this;
    }

    public function getPerception(): int
    {
        return $this->perception;
    }

    public function setPerception(int $perception): self
    {
        $this->perception = $perception;

        return $this;
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

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): self
    {
        $this->stamina = $stamina;

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

    public function getGripX(): ?float
    {
        return $this->gripX;
    }

    public function setGripX(float $gripX): self
    {
        $this->gripX = $gripX;

        return $this;
    }

    public function getGripY(): ?float
    {
        return $this->gripY;
    }

    public function setGripY(float $gripY): self
    {
        $this->gripY = $gripY;

        return $this;
    }

    public function getGripAngle(): ?int
    {
        return $this->gripAngle;
    }

    public function setGripAngle(int $gripAngle): self
    {
        $this->gripAngle = $gripAngle;

        return $this;
    }

    public function getGripScale(): ?float
    {
        return $this->gripScale;
    }

    public function setGripScale(float $gripScale): self
    {
        $this->gripScale = $gripScale;

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
}
