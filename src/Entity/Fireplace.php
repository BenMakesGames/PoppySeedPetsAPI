<?php
declare(strict_types=1);

namespace App\Entity;

use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table]
#[ORM\Index(name: 'heat_index', columns: ['heat'])]
#[ORM\Index(name: 'alcohol_index', columns: ['alcohol'])]
#[ORM\Index(name: 'longest_streak_index', columns: ['longest_streak'])]
#[ORM\Entity]
class Fireplace
{
    public const MAX_HEAT = 3 * 24 * 60; // 3 days

    public const STOCKING_APPEARANCES = [
        'fluffed',
        'tasseled',
        'snowflaked',
        'forest',
        'cow',
        'eye',
        'holly'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'fireplace')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myFireplace"])]
    #[ORM\Column(type: 'integer')]
    private $longestStreak = 0;

    #[Groups(["myFireplace"])]
    #[ORM\Column(type: 'integer')]
    private $currentStreak = 0;

    #[ORM\Column(type: 'integer')]
    private $heat = 0;

    #[ORM\Column(type: 'integer')]
    private $points = 0;

    #[Groups(["myFireplace"])]
    #[ORM\Column(type: 'smallint')]
    private $mantleSize = 12;

    #[ORM\Column(type: 'string', length: 20)]
    private $stockingAppearance;

    #[ORM\Column(type: 'string', length: 6)]
    private $stockingColorA;

    #[ORM\Column(type: 'string', length: 6)]
    private $stockingColorB;

    #[Groups(["helperPet"])]
    #[ORM\OneToOne(targetEntity: Pet::class, cascade: ['persist', 'remove'])]
    private $helper;

    #[ORM\Column(type: 'integer')]
    private $soot = 0;

    #[ORM\Column(type: 'integer')]
    private $alcohol = 0;

    #[ORM\Column(type: 'integer')]
    private $gnomePoints = 0;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version;

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

    public function getHeat(): int
    {
        return $this->heat;
    }

    public function addFuel(int $fuel, int $alcohol): self
    {
        $heatToAdd = min($fuel, self::MAX_HEAT - $this->heat);

        $this->heat += $heatToAdd;
        $this->alcohol += $alcohol;

        if($this->getHelper())
            $this->soot += $heatToAdd;

        return $this;
    }

    #[Groups(["myFireplace"])]
    #[SerializedName('heat')]
    public function getHeatPercent(): int
    {
        if($this->heat <= 0)
            return 0;
        else
            return (int)max(1, $this->heat * 100 / self::MAX_HEAT);
    }

    #[Groups(["myFireplace"])]
    public function getHeatDescription(): ?string
    {
        if($this->getHeat() <= 0)
            return null;

        $words = [];

        if($this->getHeatPercent() >= 90)
            $words[] = 'overwhelming';
        else if($this->getHeatPercent() >= 80)
            $words[] = 'slightly-intimidating';
        else if($this->getHeatPercent() >= 70)
            $words[] = 'very strong';
        else if($this->getHeatPercent() >= 60)
            $words[] = 'strong';
        else if($this->getHeatPercent() >= 50)
            $words[] = 'sizable';
        else if($this->getHeatPercent() >= 30)
            $words[] = 'medium';
        else if($this->getHeatPercent() >= 20)
            $words[] = 'small';
        else if($this->getHeatPercent() >= 10)
            $words[] = 'very small';
        else if($this->getHeatPercent() >= 5)
            $words[] = 'faintly-glowing';
        else
            $words[] = 'only technically warm';

        $percentAlcohol = $this->getAlcohol() / $this->getHeat();

        if($percentAlcohol >= 0.5)
            $words[] = 'exceptionally-boozy';
        else if($percentAlcohol >= 0.4)
            $words[] = 'highly boozy';
        else if($percentAlcohol >= 0.3)
            $words[] = 'very boozy';
        else if($percentAlcohol >= 0.2)
            $words[] = 'a bit boozy';
        else if($percentAlcohol >= 0.1)
            $words[] = 'booze-tinged';
        else if($this->getAlcohol() > 0)
            $words[] = 'ever-so-slightly boozy';

        $butOrAnd = $this->getHeat() < 4 * 60 && $percentAlcohol >= 0.3
            ? ', but '
            : ', and '
        ;

        return ArrayFunctions::list_nice($words, ', ', $butOrAnd);
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

    public function spendGnomePoints(int $points): self
    {
        if($points > $this->gnomePoints)
            throw new \InvalidArgumentException('Cannot spend more Gnome points than you have!');

        $this->gnomePoints -= $points;

        return $this;
    }

    #[Groups(["myFireplace"])]
    public function getHasReward(): bool
    {
        return $this->points > 8 * 60;
    }

    #[Groups(["myFireplace"])]
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

    public function getHelper(): ?Pet
    {
        return $this->helper;
    }

    public function setHelper(?Pet $helper): self
    {
        $this->helper = $helper;

        return $this;
    }

    public function getSoot(): ?int
    {
        return $this->soot;
    }

    public function cleanSoot(int $soot): self
    {
        $this->soot = max(0, $this->soot - $soot);

        return $this;
    }

    public function getAlcohol(): int
    {
        return $this->alcohol;
    }

    public function setAlcohol(int $alcohol): self
    {
        $this->alcohol = $alcohol;

        return $this;
    }

    public function getGnomePoints(): ?int
    {
        return $this->gnomePoints;
    }

    public function setGnomePoints(int $gnomePoints): self
    {
        $this->gnomePoints = $gnomePoints;

        return $this;
    }
}
