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
     * @ORM\Column(type="integer")
     */
    private $heightInKilometers = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward100m = 0;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $rewardExtra = [];

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

    public function getHeightInKilometers(): int
    {
        return $this->heightInKilometers;
    }

    public function increaseHeightInCentimeters(int $centimeters): self
    {
        $this->heightInCentimeters += $centimeters;

        while($this->heightInCentimeters > 100000)
        {
            $this->heightInCentimeters -= 100000;
            $this->heightInKilometers++;
        }

        return $this;
    }

    public function getReward100m(): int
    {
        return $this->reward100m;
    }

    public function incrementReward100m(): self
    {
        $this->reward100m++;

        return $this;
    }

    public function getRewardExtra(): ?array
    {
        return $this->rewardExtra;
    }

    public function setRewardExtra(array $rewardExtra): self
    {
        $this->rewardExtra = $rewardExtra;

        return $this;
    }

    public function addRewardExtra(string $rewardExtra): self
    {
        $this->rewardExtra[] = $rewardExtra;

        return $this;
    }

    public function clearRewardExtra(): self
    {
        $this->rewardExtra = [];

        return $this;
    }
}
