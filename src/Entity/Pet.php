<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRepository")
 */
class Pet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myPets", "publicProfile"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pets")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPets", "publicProfile"})
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
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPets", "publicProfile"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPets", "publicProfile"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPets", "publicProfile"})
     */
    private $colorB;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", cascade={"persist", "remove"})
     * @Groups({"myPets", "publicProfile"})
     */
    private $hat;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPets", "publicProfile"})
     */
    private $isDead = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $junk = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $whack = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"myPets", "publicProfile"})
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

    public function __construct()
    {
        $this->birthDate = new \DateTimeImmutable();
        $this->lastInteracted = (new \DateTimeImmutable())->modify('-3 days');
        $this->stomachSize = mt_rand(12, 24);
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

    public function getTime(): ?int
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

    public function getFood(): ?int
    {
        return $this->food;
    }

    public function increaseFood(int $amount): self
    {
        if($amount === 0) return $this;

        $this->food = min($this->food + $amount, $this->getStomachSize());

        return $this;
    }

    public function getSafety(): ?int
    {
        return $this->safety;
    }

    public function increaseSafety(int $amount): self
    {
        if($amount === 0) return $this;

        if($amount < 0 || ($this->getFood() + $this->getWhack() > 0))
            $this->safety = max(-$this->getMaxSafety(), min($this->safety + $amount, $this->getMaxSafety()));

        return $this;
    }

    public function getMaxSafety(): int
    {
        return 24;
    }

    public function getLove(): ?int
    {
        return $this->love;
    }

    public function increaseLove(int $amount): self
    {
        if($amount === 0) return $this;

        if($amount < 0 || ($this->getFood() + $this->getWhack() > 0 && $this->getSafety() + $this->getWhack() > 0))
            $this->love = max(-$this->getMaxLove(), min($this->love + $amount, $this->getMaxLove()));

        return $this;
    }

    public function getMaxLove(): int
    {
        return 24;
    }

    public function getEsteem(): ?int
    {
        return $this->esteem;
    }

    public function increaseEsteem(int $amount): self
    {
        if($amount === 0) return $this;

        if($amount < 0 || ($this->getFood() + $this->getWhack() > 0 && $this->getSafety() + $this->getWhack() > 0 && $this->getLove() + $this->getWhack() > 0))
            $this->esteem = max(-$this->getMaxEsteem(), min($this->esteem + $amount, $this->getMaxEsteem()));

        return $this;
    }

    public function getMaxEsteem(): int
    {
        return 24;
    }

    public function getExperience(): ?int
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getHat(): ?Inventory
    {
        return $this->hat;
    }

    public function setHat(?Inventory $hat): self
    {
        $this->hat = $hat;

        return $this;
    }

    public function getIsDead(): ?bool
    {
        return $this->isDead;
    }

    public function setIsDead(bool $isDead): self
    {
        $this->isDead = $isDead;

        return $this;
    }

    public function getJunk(): ?int
    {
        return $this->junk;
    }

    public function increaseJunk(int $amount): self
    {
        if($amount === 0) return $this;

        $this->junk = max(0, min($this->junk + $amount, $this->getStomachSize()));

        return $this;
    }

    public function getWhack(): ?int
    {
        return $this->whack;
    }

    public function increaseWhack(int $amount): self
    {
        if($amount === 0) return $this;

        $this->whack = max(0, min($this->whack + $amount, $this->getStomachSize()));

        return $this;
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
     * @Groups({"myPets"})
     */
    public function getFull(): string
    {
        $fullness = ($this->getFood() + $this->getJunk() + $this->getWhack()) / $this->getStomachSize();

        if($fullness >= 0.75)
        {
            if(substr($this->getImage(), 5) === 'fish/')
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
     * @Groups({"myPets"})
     */
    public function getSafe(): string
    {
        if($this->getSafety() >= 16)
            return 'untouchable';
        else if($this->getSafety() >= 8)
            return 'safe';
        else if($this->getSafety() >= -8)
            return '...';
        else if($this->getSafety() >= -16)
            return 'on edge';
        else
            return 'terrified';
    }

    /**
     * @Groups({"myPets"})
     */
    public function getLoved(): string
    {
        if($this->getLove() >= 16)
            return 'very loved';
        else if($this->getLove() >= 8)
            return 'loved';
        else if($this->getLove() >= -8)
            return '...';
        else if($this->getLove() >= -16)
            return 'lonely';
        else
            return 'hated';
    }

    /**
     * @Groups({"myPets"})
     */
    public function getEsteemed(): string
    {
        if($this->getEsteem() >= 16)
            return 'amazing';
        else if($this->getLove() >= 8)
            return 'accomplished';
        else if($this->getLove() >= -8)
            return '...';
        else if($this->getEsteem() >= -16)
            return 'useless';
        else
            return 'depressed';
    }

    public function getStomachSize(): int
    {
        return $this->stomachSize;
    }

    public function getLastInteracted(): ?\DateTimeImmutable
    {
        return $this->lastInteracted;
    }

    public function setLastInteracted(\DateTimeImmutable $lastInteracted): self
    {
        $this->lastInteracted = $lastInteracted;

        return $this;
    }

    /**
     * @Groups({"myPets"})
     */
    public function getCanInteract(): bool
    {
        return $this->getLastInteracted() < (new \DateTimeImmutable())->modify('-30 minutes');
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
     * @Groups({"myPets"})
     */
    public function getLevel(): int
    {
        return $this->getSkills()->getTotal();
    }

    public function getExperienceToLevel(): int
    {
        return $this->getLevel() * 10;
    }
}
