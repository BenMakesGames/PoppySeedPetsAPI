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
     * @Groups({"myPets"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pets")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPets"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", name="`time`")
     */
    private $time = 60;

    /**
     * @ORM\Column(type="integer")
     */
    private $food = 4;

    /**
     * @ORM\Column(type="integer")
     */
    private $safety = 4;

    /**
     * @ORM\Column(type="integer")
     */
    private $love = 4;

    /**
     * @ORM\Column(type="integer")
     */
    private $esteem = 4;

    /**
     * @ORM\Column(type="integer")
     */
    private $experience = 0;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPets"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPets"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"myPets"})
     */
    private $colorB;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", cascade={"persist", "remove"})
     * @Groups({"myPets"})
     */
    private $hat;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPets"})
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
     * @Groups({"myPets"})
     */
    private $birthDate;

    public function __construct()
    {
        $this->birthDate = new \DateTimeImmutable();
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

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getFood(): ?int
    {
        return $this->food;
    }

    public function setFood(int $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getSafety(): ?int
    {
        return $this->safety;
    }

    public function setSafety(int $safety): self
    {
        $this->safety = $safety;

        return $this;
    }

    public function getLove(): ?int
    {
        return $this->love;
    }

    public function setLove(int $love): self
    {
        $this->love = $love;

        return $this;
    }

    public function getEsteem(): ?int
    {
        return $this->esteem;
    }

    public function setEsteem(int $esteem): self
    {
        $this->esteem = $esteem;

        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): self
    {
        $this->experience = $experience;

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

    public function setJunk(int $junk): self
    {
        $this->junk = $junk;

        return $this;
    }

    public function getWhack(): ?int
    {
        return $this->whack;
    }

    public function setWhack(int $whack): self
    {
        $this->whack = $whack;

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
}
