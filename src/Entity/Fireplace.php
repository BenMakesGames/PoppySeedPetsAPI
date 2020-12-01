<?php

namespace App\Entity;

use App\Functions\ArrayFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FireplaceRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="heat_index", columns={"heat"}),
 *     @ORM\Index(name="longest_streak_index", columns={"longest_streak"})
 * })
 */
class Fireplace
{
    public const MAX_HEAT = 3 * 24 * 60; // 3 days

    public const STOCKING_APPEARANCES = [
        'fluffed',
        'tasseled'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="fireplace")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myFireplace"})
     */
    private $longestStreak = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myFireplace"})
     */
    private $currentStreak = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $heat = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $points = 0;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $whelpName;

    /**
     * @ORM\Column(type="integer")
     */
    private $whelpFood = 0;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $whelpColorA;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $whelpColorB;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myFireplace"})
     */
    private $mantleSize = 12;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $stockingAppearance;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $stockingColorA;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $stockingColorB;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLongestStreak(): ?int
    {
        return $this->longestStreak;
    }

    public function getCurrentStreak(): ?int
    {
        return $this->currentStreak;
    }

    public function getHeat(): ?int
    {
        return $this->heat;
    }

    public function addHeat(int $heat): self
    {
        $this->heat = min($this->heat + $heat, self::MAX_HEAT);

        return $this;
    }

    /**
     * @Groups({"myFireplace"})
     */
    public function getHeatDescription(): ?string
    {
        if($this->getHeat() >= 2.5 * 24 * 60)
            return 'overwhelming';
        else if($this->getHeat() >= 2 * 24 * 60)
            return 'slightly-intimidating';
        else if($this->getHeat() >= 1.5 * 24 * 60)
            return 'very strong';
        else if($this->getHeat() >= 24 * 60)
            return 'strong';
        else if($this->getHeat() >= 16 * 60)
            return 'sizable';
        else if($this->getHeat() >= 8 * 60)
            return 'medium';
        else if($this->getHeat() >= 4 * 60)
            return 'small';
        else if($this->getHeat() >= 2 * 60)
            return 'very small';
        else if($this->getHeat() > 60)
            return 'faintly-glowing';
        else if($this->getHeat() > 0)
            return 'only technically warm';
        else
            return null;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function clearPoints(): self
    {
        $this->points = 0;

        return $this;
    }

    public function spendPoints(int $points): self
    {
        if($points > $this->points)
            throw new \InvalidArgumentException('Cannot spend more points than you have!');

        $this->points -= $points;

        return $this;
    }

    /**
     * @Groups({"myFireplace"})
     */
    public function getHasReward(): bool
    {
        return $this->points > 8 * 60;
    }

    public function getWhelpName(): ?string
    {
        return $this->whelpName;
    }

    public function setWhelpName(?string $whelpName): self
    {
        $this->whelpName = $whelpName;

        return $this;
    }

    public function getWhelpFood(): int
    {
        return $this->whelpFood;
    }

    public function increaseWhelpFood(int $food): self
    {
        $this->whelpFood += $food;

        return $this;
    }

    public function getWhelpColorA(): ?string
    {
        return $this->whelpColorA;
    }

    public function setWhelpColorA(string $whelpColorA): self
    {
        $this->whelpColorA = $whelpColorA;

        return $this;
    }

    public function getWhelpColorB(): ?string
    {
        return $this->whelpColorB;
    }

    public function setWhelpColorB(string $whelpColorB): self
    {
        $this->whelpColorB = $whelpColorB;

        return $this;
    }

    /**
     * @Groups({"myFireplace"})
     */
    public function getWhelp()
    {
        if($this->getWhelpName())
        {
            return [
                'name' => $this->getWhelpName(),
                'colorA' => $this->getWhelpColorA(),
                'colorB' => $this->getWhelpColorB()
            ];
        }
        else
            return null;
    }

    /**
     * @Groups({"myFireplace"})
     */
    public function getStocking()
    {
        return [
            'appearance' => $this->getStockingAppearance(),
            'colorA' => $this->getStockingColorA(),
            'colorB' => $this->getStockingColorB()
        ];
    }

    public function getMantleSize(): int
    {
        return $this->mantleSize;
    }

    public function setMantleSize(int $mantleSize): self
    {
        $this->mantleSize = $mantleSize;

        return $this;
    }

    public function getStockingAppearance(): ?string
    {
        return $this->stockingAppearance;
    }

    public function setStockingAppearance(string $stockingAppearance): self
    {
        $this->stockingAppearance = $stockingAppearance;

        return $this;
    }

    public function getStockingColorA(): ?string
    {
        return $this->stockingColorA;
    }

    public function setStockingColorA(string $stockingColorA): self
    {
        $this->stockingColorA = $stockingColorA;

        return $this;
    }

    public function getStockingColorB(): ?string
    {
        return $this->stockingColorB;
    }

    public function setStockingColorB(string $stockingColorB): self
    {
        $this->stockingColorB = $stockingColorB;

        return $this;
    }
}
