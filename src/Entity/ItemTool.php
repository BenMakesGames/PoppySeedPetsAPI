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
    private $strength;

    /**
     * @ORM\Column(type="integer")
     */
    private $dexterity;

    /**
     * @ORM\Column(type="integer")
     */
    private $intelligence;

    /**
     * @ORM\Column(type="integer")
     */
    private $perception;

    /**
     * @ORM\Column(type="integer")
     */
    private $stealth;

    /**
     * @ORM\Column(type="integer")
     */
    private $stamina;

    /**
     * @ORM\Column(type="integer")
     */
    private $nature;

    /**
     * @ORM\Column(type="integer")
     */
    private $brawl;

    /**
     * @ORM\Column(type="integer")
     */
    private $umbra;

    /**
     * @ORM\Column(type="integer")
     */
    private $crafts;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripY;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile"})
     */
    private $gripAngle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;

        return $this;
    }

    public function getDexterity(): ?int
    {
        return $this->dexterity;
    }

    public function setDexterity(int $dexterity): self
    {
        $this->dexterity = $dexterity;

        return $this;
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): self
    {
        $this->intelligence = $intelligence;

        return $this;
    }

    public function getPerception(): ?int
    {
        return $this->perception;
    }

    public function setPerception(int $perception): self
    {
        $this->perception = $perception;

        return $this;
    }

    public function getStealth(): ?int
    {
        return $this->stealth;
    }

    public function setStealth(int $stealth): self
    {
        $this->stealth = $stealth;

        return $this;
    }

    public function getStamina(): ?int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): self
    {
        $this->stamina = $stamina;

        return $this;
    }

    public function getNature(): ?int
    {
        return $this->nature;
    }

    public function setNature(int $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    public function getBrawl(): ?int
    {
        return $this->brawl;
    }

    public function setBrawl(int $brawl): self
    {
        $this->brawl = $brawl;

        return $this;
    }

    public function getUmbra(): ?int
    {
        return $this->umbra;
    }

    public function setUmbra(int $umbra): self
    {
        $this->umbra = $umbra;

        return $this;
    }

    public function getCrafts(): ?int
    {
        return $this->crafts;
    }

    public function setCrafts(int $crafts): self
    {
        $this->crafts = $crafts;

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
}
