<?php
declare(strict_types=1);

namespace App\Entity;

use App\Service\Squirrel3;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\PetSkillsRepository')]
class PetSkills
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $strength = 0;

    #[ORM\Column(type: 'integer')]
    private $dexterity = 0;

    #[ORM\Column(type: 'integer')]
    private $intelligence = 0;

    #[ORM\Column(type: 'integer')]
    private $perception = 0;

    #[ORM\Column(type: 'integer')]
    private $stealth = 0;

    #[ORM\Column(type: 'integer')]
    private $stamina = 0;

    #[ORM\Column(type: 'integer')]
    private $nature = 0;

    #[ORM\Column(type: 'integer')]
    private $brawl = 0;

    #[ORM\Column(type: 'integer')]
    private $arcana = 0;

    #[ORM\Column(type: 'integer')]
    private $crafts = 0;

    #[ORM\Column(type: 'integer')]
    private $music = 0;

    #[ORM\Column(type: 'integer')]
    private $science = 0;

    #[ORM\OneToOne(targetEntity: Pet::class, mappedBy: 'skills')]
    private $pet;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $talent;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $expertise;

    #[ORM\Column(type: 'integer')]
    private $scrollLevels = 0;

    public function __construct()
    {
        for($x = 0; $x < 5; $x++)
        {
            switch(random_int(1, 5))
            {
                case 1: $this->strength++; break;
                case 2: $this->intelligence++; break;
                case 3: $this->stamina++; break;
                case 4: $this->perception++; break;
                case 5: $this->dexterity++; break;
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): int
    {
        return
            $this->nature + $this->brawl + $this->arcana + $this->stealth + $this->crafts + $this->music + $this->science
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

    public function decreaseStat(string $statName): self
    {
        if($statName === 'id' || $statName === 'pet' || !property_exists($this, $statName))
            throw new \InvalidArgumentException('Unknown stat "' . $statName . '".');

        if($this->{$statName} > 0)
            $this->{$statName}--;

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

    public function getArcana(): int
    {
        return $this->arcana;
    }

    public function setArcana(int $arcana): self
    {
        $this->arcana = $arcana;

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

    public function getScience(): int
    {
        return $this->science;
    }

    public function setScience(int $science): self
    {
        $this->science = $science;

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

    public function getTalent(): ?\DateTimeImmutable
    {
        return $this->talent;
    }

    public function setTalent(): self
    {
        $this->talent = new \DateTimeImmutable();

        return $this;
    }

    public function getExpertise(): ?\DateTimeImmutable
    {
        return $this->expertise;
    }

    public function setExpertise(): self
    {
        $this->expertise = new \DateTimeImmutable();

        return $this;
    }

    public function getScrollLevels(): ?int
    {
        return $this->scrollLevels;
    }

    public function increaseScrollLevels(): self
    {
        $this->scrollLevels++;

        return $this;
    }
}
