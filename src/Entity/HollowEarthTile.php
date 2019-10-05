<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HollowEarthTileRepository")
 */
class HollowEarthTile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HollowEarthZone")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"hollowEarth"})
     */
    private $zone;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"hollowEarth"})
     */
    private $x;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"hollowEarth"})
     */
    private $y;

    /**
     * @ORM\Column(type="json")
     */
    private $event = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $requiredAction = 0;

    /**
     * @ORM\Column(type="string", length=1)
     * @Groups({"hollowEarth"})
     */
    private $moveDirection;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZone(): ?HollowEarthZone
    {
        return $this->zone;
    }

    public function setZone(?HollowEarthZone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getEvent(): ?array
    {
        return $this->event;
    }

    public function setEvent(array $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getRequiredAction(): int
    {
        return $this->requiredAction;
    }

    public function setRequiredAction(int $requiredAction): self
    {
        $this->requiredAction = $requiredAction;

        return $this;
    }

    public function getMoveDirection(): ?string
    {
        return $this->moveDirection;
    }

    public function setMoveDirection(string $moveDirection): self
    {
        $this->moveDirection = $moveDirection;

        return $this;
    }
}
