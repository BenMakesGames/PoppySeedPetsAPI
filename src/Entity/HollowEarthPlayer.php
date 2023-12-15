<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthMoveDirectionEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\HollowEarthPlayerRepository')]
class HollowEarthPlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'hollowEarthPlayer')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["hollowEarth"])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\HollowEarthTile')]
    #[ORM\JoinColumn(nullable: false)]
    private $currentTile;

    #[ORM\Column(type: 'json', nullable: true)]
    private array|null $currentAction = null;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $movesRemaining = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\OneToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $chosenPet = null;

    #[ORM\Column(type: 'string', length: 1)]
    private $currentDirection;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $jade = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $incense = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $salt = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $amber = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'integer')]
    private $fruit = 0;

    #[Groups(["hollowEarth"])]
    #[ORM\Column(type: 'boolean')]
    private $showGoods = false;

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

    public function getCurrentTile(): HollowEarthTile
    {
        return $this->currentTile;
    }

    public function setCurrentTile(HollowEarthTile $currentTile): self
    {
        $this->currentTile = $currentTile;
        $this->currentDirection = $currentTile->getMoveDirection();

        return $this;
    }

    public function getCurrentAction(): ?array
    {
        return $this->currentAction;
    }

    public function setCurrentAction(?array $currentAction): self
    {
        if($currentAction !== null && count($currentAction) === 0)
            $currentAction = null;

        $this->currentAction = $currentAction;

        return $this;
    }

    public function getMovesRemaining(): int
    {
        return $this->movesRemaining;
    }

    public function decreaseMovesRemaining(): self
    {
        $this->movesRemaining--;
        return $this;
    }

    public function setMovesRemaining(int $movesRemaining): self
    {
        $this->movesRemaining = $movesRemaining;

        return $this;
    }

    public function getChosenPet(): ?Pet
    {
        return $this->chosenPet;
    }

    public function setChosenPet(?Pet $chosenPet): self
    {
        $this->chosenPet = $chosenPet;

        return $this;
    }

    #[Groups(["hollowEarth"])]
    public function getAction()
    {
        if($this->currentAction === null)
            return null;

        $action = [];

        if(array_key_exists('type', $this->currentAction))
        {
            $action['type'] = $this->currentAction['type'];

            switch($action['type'])
            {
                case HollowEarthActionTypeEnum::PAY_ITEM:
                    $action['item'] = $this->currentAction['item'];
                    break;
                case HollowEarthActionTypeEnum::PAY_MONEY:
                    $action['amount'] = $this->currentAction['amount'];
                    break;
                case HollowEarthActionTypeEnum::PAY_ITEM_AND_MONEY:
                    $action['item'] = $this->currentAction['item'];
                    $action['amount'] = $this->currentAction['amount'];
                    break;
                case HollowEarthActionTypeEnum::CHOOSE_ONE:
                    $action['buttons'] = $this->currentAction['buttons'];
                    break;
            }
        }

        if(array_key_exists('description', $this->currentAction))
            $action['description'] = $this->currentAction['description'];

        if(array_key_exists('buttonText', $this->currentAction))
            $action['buttonText'] = $this->currentAction['buttonText'];

        return $action;
    }

    public function getCurrentDirection(): string
    {
        return $this->currentDirection;
    }

    public function setCurrentDirection(string $currentDirection): self
    {
        if(!HollowEarthMoveDirectionEnum::isAValue($currentDirection))
            throw new EnumInvalidValueException(HollowEarthMoveDirectionEnum::class, $currentDirection);

        $this->currentDirection = $currentDirection;

        return $this;
    }

    public function getJade(): int
    {
        return $this->jade;
    }

    public function increaseJade(int $jade): self
    {
        $this->jade += $jade;

        return $this;
    }

    public function getIncense(): int
    {
        return $this->incense;
    }

    public function increaseIncense(int $incense): self
    {
        $this->incense += $incense;

        return $this;
    }

    public function getSalt(): int
    {
        return $this->salt;
    }

    public function increaseSalt(int $salt): self
    {
        $this->salt += $salt;

        return $this;
    }

    public function getAmber(): int
    {
        return $this->amber;
    }

    public function increaseAmber(int $amber): self
    {
        $this->amber += $amber;

        return $this;
    }

    public function getFruit(): int
    {
        return $this->fruit;
    }

    public function increaseFruit(int $fruit): self
    {
        $this->fruit += $fruit;

        return $this;
    }

    public function getShowGoods(): bool
    {
        return $this->showGoods;
    }

    public function setShowGoods(): self
    {
        $this->showGoods = true;

        return $this;
    }
}
