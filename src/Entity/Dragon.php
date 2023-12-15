<?php

namespace App\Entity;

use App\Repository\DragonRepository;
use App\Service\Squirrel3;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DragonRepository::class)]
class Dragon
{
    // as a whelp:
    public const FOOD_REQUIRED_FOR_A_MEAL = 35;
    public const FOOD_REQUIRED_TO_GROW = 35 * 20;

    // as an adult:
    public const APPEARANCE_IMAGES = [ 1, 2, 3, 4 ];
    public const GREETINGS_AND_THANKS = [
        [
            'greeting' => 'Hello, friend.',
            'thanks' => 'Thank you, friend.',
        ],
        [
            'greeting' => 'Always good to see you.',
            'thanks' => 'A dragon never forgets acts of generosity.',
        ],
        [
            'greeting' => 'Ah. You came.',
            'thanks' => 'It shines. Beautiful.',
        ],
        [
            'greeting' => 'What brings you to my den today?',
            'thanks' => 'Ah. A fine addition to my hoard.',
        ],
        [
            'greeting' => 'I was expecting you.',
            'thanks' => 'A fair exchange.',
        ],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[Groups(["myFireplace", "myDragon"])]
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $food = 0;

    #[Groups(["myFireplace", "myDragon"])]
    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private $colorA;

    #[Groups(["myFireplace", "myDragon"])]
    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private $colorB;

    #[ORM\Column(type: 'boolean')]
    private $isAdult = false;

    #[ORM\Column(type: 'integer')]
    private $growth = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups(['myDragon'])]
    private $silver = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups(['myDragon'])]
    private $gold = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups(['myDragon'])]
    private $gems = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['myDragon'])]
    private $greetings = [];

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['myDragon'])]
    private $thanks = [];

    #[ORM\Column(type: 'smallint')]
    #[Groups(['myDragon'])]
    private $appearance;

    #[Groups(["helperPet"])]
    #[ORM\OneToOne(targetEntity: Pet::class, cascade: ['persist', 'remove'])]
    private $helper;

    #[ORM\Column(type: 'float')]
    private $earnings = 0;

    #[ORM\Column(type: 'float')]
    private $byproductProgress = 0;

    #[ORM\OneToOne(targetEntity: DragonHostage::class, mappedBy: 'dragon')]
    #[Groups(['myDragon'])]
    private $hostage;

    public function __construct()
    {
        $squirrel3 = new Squirrel3();

        $this->appearance = $squirrel3->rngNextFromArray(self::APPEARANCE_IMAGES);
    }

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

    #[Groups(["myFireplace"])]
    public function getGrowthPercent(): float
    {
        return $this->growth / self::FOOD_REQUIRED_TO_GROW;
    }

    public function getSilver(): ?int
    {
        return $this->silver;
    }

    public function increaseSilver(int $silver): self
    {
        $this->silver += $silver;

        return $this;
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function increaseGold(int $gold): self
    {
        $this->gold += $gold;

        return $this;
    }

    public function getGems(): ?int
    {
        return $this->gems;
    }

    public function increaseGems(int $gems): self
    {
        $this->gems += $gems;

        return $this;
    }

    public function getGreetings(): ?array
    {
        return $this->greetings;
    }

    public function setGreetings(?array $greetings): self
    {
        $this->greetings = $greetings;

        return $this;
    }

    public function getThanks(): ?array
    {
        return $this->thanks;
    }

    public function setThanks(?array $thanks): self
    {
        $this->thanks = $thanks;

        return $this;
    }

    public function getAppearance(): ?int
    {
        return $this->appearance;
    }

    public function setAppearance(int $appearance): self
    {
        $this->appearance = $appearance;

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

    public function getEarnings(): ?float
    {
        return $this->earnings;
    }

    public function addEarnings(float $earnings): self
    {
        $this->earnings += $earnings;

        return $this;
    }

    public function getByproductProgress(): ?float
    {
        return $this->byproductProgress;
    }

    public function addByproductProgress(float $byproductProgress): self
    {
        $this->byproductProgress += $byproductProgress;

        return $this;
    }

    public function getHostage(): ?DragonHostage
    {
        return $this->hostage;
    }

    public function setHostage(?DragonHostage $hostage): self
    {
        // set the owning side of the relation if necessary
        if ($hostage !== null && $hostage->getDragon() !== $this) {
            $hostage->setDragon($this);
        }

        $this->hostage = $hostage;

        return $this;
    }
}
