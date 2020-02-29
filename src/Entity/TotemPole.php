<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TotemPoleRepository")
 */
class TotemPole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="totemPole", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="integer")
     */
    private $height = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $heightInCentimeters = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reward10m = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reward50m = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward100m = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reward9000m = false;

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

    public function getHeight(): int
    {
        return $this->height;
    }

    public function increaseHeight(): self
    {
        $this->height++;

        return $this;
    }

    public function getHeightInCentimeters(): int
    {
        return $this->heightInCentimeters;
    }

    public function increaseHeightInCentimeters(int $centimeters): self
    {
        $this->heightInCentimeters += $centimeters;

        return $this;
    }

    public function getReward10m(): bool
    {
        return $this->reward10m;
    }

    public function setReward10m(bool $reward10m): self
    {
        $this->reward10m = $reward10m;

        return $this;
    }

    public function getReward50m(): bool
    {
        return $this->reward50m;
    }

    public function setReward50m(bool $reward50m): self
    {
        $this->reward50m = $reward50m;

        return $this;
    }

    public function getReward100m(): int
    {
        return $this->reward100m;
    }

    public function setReward100m(int $reward100m): self
    {
        $this->reward100m = $reward100m;

        return $this;
    }

    public function getReward9000m(): ?bool
    {
        return $this->reward9000m;
    }

    public function setReward9000m(bool $reward9000m): self
    {
        $this->reward9000m = $reward9000m;

        return $this;
    }
}
