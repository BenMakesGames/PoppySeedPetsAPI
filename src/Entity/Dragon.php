<?php

namespace App\Entity;

use App\Repository\DragonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DragonRepository::class)
 */
class Dragon
{
    // as a whelp:
    public const FOOD_REQUIRED_FOR_A_MEAL = 35;
    public const FOOD_REQUIRED_TO_GROW = 35 * 20;

    // as an adult:

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"myFireplace", "myDragon"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $food = 0;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     * @Groups({"myFireplace", "myDragon"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     * @Groups({"myFireplace", "myDragon"})
     */
    private $colorB;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdult = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $growth = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myDragon"})
     */
    private $silver;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myDragon"})
     */
    private $gold;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myDragon"})
     */
    private $gems;

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

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFood(): int
    {
        return $this->food;
    }

    public function increaseFood(int $food): self
    {
        $this->food += $food;
        $this->growth += $food;

        return $this;
    }

    public function decreaseFood(): self
    {
        $this->food -= self::FOOD_REQUIRED_FOR_A_MEAL;

        return $this;
    }

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }

    public function getIsAdult(): bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(bool $isAdult): self
    {
        $this->isAdult = $isAdult;

        return $this;
    }

    public function getGrowth(): int
    {
        return $this->growth;
    }

    /**
     * @Groups({"myFireplace"})
     */
    public function getGrowthPercent(): float
    {
        return $this->growth / self::FOOD_REQUIRED_TO_GROW;
    }

    public function getSilver(): ?int
    {
        return $this->silver;
    }

    public function setSilver(int $silver): self
    {
        $this->silver = $silver;

        return $this;
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function setGold(int $gold): self
    {
        $this->gold = $gold;

        return $this;
    }

    public function getGems(): ?int
    {
        return $this->gems;
    }

    public function setGems(int $gems): self
    {
        $this->gems = $gems;

        return $this;
    }
}
