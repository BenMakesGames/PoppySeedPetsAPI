<?php

namespace App\Entity;

use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthMoveDirectionEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HollowEarthPlayerRepository")
 */
class HollowEarthPlayer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="hollowEarthPlayer", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HollowEarthTile")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"hollowEarth"})
     */
    private $currentTile;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $currentAction = null;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"hollowEarth"})
     */
    private $movesRemaining = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @Groups({"hollowEarth"})
     */
    private $chosenPet = null;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $currentDirection;

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

    public function getCurrentTile(): ?HollowEarthTile
    {
        return $this->currentTile;
    }

    public function setCurrentTile(?HollowEarthTile $currentTile): self
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

    public function getMovesRemaining(): ?int
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

    /**
     * @Groups({"hollowEarth"})
     */
    public function getAction()
    {
        if($this->currentAction === null)
            return null;
        else
        {
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
                        $action['item'] = $this->currentAction['amount'];
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
    }

    public function getCurrentDirection(): string
    {
        return $this->currentDirection;
    }

    public function setCurrentDirection(string $currentDirection): self
    {
        if(!HollowEarthMoveDirectionEnum::isAValue($currentDirection))
            throw new \InvalidArgumentException('$currentDirection must be a valid HollowEarthMoveDirectionEnum value.');

        $this->currentDirection = $currentDirection;

        return $this;
    }
}
