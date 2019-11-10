<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetSkillsRepository")
 */
class PetSkills
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
    private $music = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $computer = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="skills")
     */
    private $pet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): int
    {
        return
            $this->strength + $this->stamina + $this->dexterity + $this->intelligence + $this->perception +
            $this->nature + $this->brawl + $this->umbra + $this->stealth + $this->crafts + $this->music + $this->computer
        ;
    }

    public function getStat(string $statName): int
    {
        if($statName === 'id' || $statName === 'pet' || !property_exists($this, $statName))
            throw new \InvalidArgumentException('Unknown stat "' . $statName . '".');

        return $this->{$statName};
    }

    public function increaseStat(string $statName): self
    {
        if($statName === 'id' || $statName === 'pet' || !property_exists($this, $statName))
            throw new \InvalidArgumentException('Unknown stat "' . $statName . '".');

        if($this->{$statName} < 20)
            $this->{$statName}++;

        return $this;
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

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        // set the owning side of the relation if necessary
        if ($this !== $pet->getSkills()) {
            $pet->setSkills($this);
        }

        return $this;
    }

    public function getComputer(): int
    {
        return $this->computer;
    }

    public function setComputer(int $computer): self
    {
        $this->computer = $computer;

        return $this;
    }

    public function getMusic(): ?int
    {
        return $this->music;
    }

    public function setMusic(int $music): self
    {
        $this->music = $music;

        return $this;
    }
}
