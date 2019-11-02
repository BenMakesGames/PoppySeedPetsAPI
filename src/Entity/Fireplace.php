<?php

namespace App\Entity;

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
     * @Groups({"myFireplace"})
     */
    private $heat = 0;

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

    public function setLongestStreak(int $longestStreak): self
    {
        $this->longestStreak = $longestStreak;

        return $this;
    }

    public function getCurrentStreak(): ?int
    {
        return $this->currentStreak;
    }

    public function setCurrentStreak(int $currentStreak): self
    {
        $this->currentStreak = $currentStreak;

        return $this;
    }

    public function getHeat(): ?int
    {
        return $this->heat;
    }

    public function setHeat(int $heat): self
    {
        $this->heat = $heat;

        return $this;
    }
}
