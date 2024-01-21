<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'workers_idx', columns: ['workers'])]
#[ORM\Index(name: 'flower_power_idx', columns: ['flower_power'])]
#[ORM\Entity]
class Beehive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'beehive', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myBeehive"])]
    #[ORM\Column(type: 'integer')]
    private $workers = 250;

    #[Groups(["myBeehive"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $queenName;

    #[Groups(["myBeehive"])]
    #[ORM\Column(type: 'integer')]
    private $flowerPower = 0;

    #[ORM\Column(type: 'integer')]
    private $royalJellyProgress = 0;

    #[ORM\Column(type: 'integer')]
    private $honeycombProgress = 0;

    #[ORM\Column(type: 'integer')]
    private $miscProgress = 0;

    #[ORM\Column(type: 'integer')]
    private $interactionPower = 48;

    /**
     * @Groups({"myBeehive"})
     * @var Item
     */
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $requestedItem;

    /**
     * @Groups({"myBeehive"})
     * @var Item
     */
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $alternateRequestedItem;

    #[Groups(["helperPet"])]
    #[ORM\OneToOne(targetEntity: Pet::class, cascade: ['persist', 'remove'])]
    private $helper;

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

    public function getWorkers(): int
    {
        return $this->workers;
    }

    public function addWorkers(int $workers): self
    {
        $this->workers += $workers;

        return $this;
    }

    public function getQueenName(): string
    {
        return $this->queenName;
    }

    public function setQueenName(string $queenName): self
    {
        $this->queenName = $queenName;

        return $this;
    }

    public function getFlowerPower(): int
    {
        return $this->flowerPower;
    }

    public function setFlowerPower(int $flowerPower): self
    {
        $this->flowerPower = $flowerPower;

        return $this;
    }

    public function getRoyalJellyProgress(): int
    {
        return $this->royalJellyProgress;
    }

    public function setRoyalJellyProgress(int $royalJellyProgress): self
    {
        $this->royalJellyProgress = $royalJellyProgress;

        return $this;
    }

    public function getHoneycombProgress(): int
    {
        return $this->honeycombProgress;
    }

    public function setHoneycombProgress(int $honeycombProgress): self
    {
        $this->honeycombProgress = $honeycombProgress;

        return $this;
    }

    public function getMiscProgress(): int
    {
        return $this->miscProgress;
    }

    public function setMiscProgress(int $miscProgress): self
    {
        $this->miscProgress = $miscProgress;

        return $this;
    }

    public function getInteractionPower(): int
    {
        return $this->interactionPower;
    }

    public function setInteractionPower(): self
    {
        $this->interactionPower = max(36, $this->interactionPower);

        return $this;
    }

    public function getRequestedItem(): Item
    {
        return $this->requestedItem;
    }

    public function setRequestedItem(Item $requestedItem): self
    {
        $this->requestedItem = $requestedItem;

        return $this;
    }

    #[Groups(["myBeehive"])]
    public function getRoyalJellyPercent(): float
    {
        return min(1, round($this->royalJellyProgress / 2000, 2));
    }

    #[Groups(["myBeehive"])]
    public function getHoneycombPercent(): float
    {
        return min(1, round($this->honeycombProgress / 2000, 2));
    }

    #[Groups(["myBeehive"])]
    public function getMiscPercent(): float
    {
        return min(1, round($this->miscProgress / 2000, 2));
    }

    public function getAlternateRequestedItem(): Item
    {
        return $this->alternateRequestedItem;
    }

    public function setAlternateRequestedItem(Item $alternateRequestedItem): self
    {
        $this->alternateRequestedItem = $alternateRequestedItem;

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
}
