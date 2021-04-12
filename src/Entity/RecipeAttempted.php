<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeAttemptedRepository")
 */
class RecipeAttempted
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recipe;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $firstAttemptedOn;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastAttemptedOn;

    /**
     * @ORM\Column(type="integer")
     */
    private $timesAttempted = 1;

    public function __construct()
    {
        $this->firstAttemptedOn = new \DateTimeImmutable();
        $this->lastAttemptedOn = new \DateTimeImmutable();
    }

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

    public function getRecipe(): string
    {
        return $this->recipe;
    }

    public function setRecipe(string $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getFirstAttemptedOn(): \DateTimeImmutable
    {
        return $this->firstAttemptedOn;
    }

    public function getLastAttemptedOn(): \DateTimeImmutable
    {
        return $this->lastAttemptedOn;
    }

    public function setLastAttemptedOn(): self
    {
        $this->lastAttemptedOn = new \DateTimeImmutable();

        return $this;
    }

    public function getTimesAttempted(): int
    {
        return $this->timesAttempted;
    }

    public function incrementTimesAttempted(): self
    {
        $this->timesAttempted++;

        return $this;
    }
}
