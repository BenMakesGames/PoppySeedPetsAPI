<?php

namespace App\Entity;

use App\Repository\PetCravingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetCravingRepository::class)
 */
class PetCraving
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Pet::class, inversedBy="craving")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\ManyToOne(targetEntity=ItemGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $foodGroup;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdOn;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $satisfiedOn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getFoodGroup(): ?ItemGroup
    {
        return $this->foodGroup;
    }

    public function setFoodGroup(?ItemGroup $foodGroup): self
    {
        $this->foodGroup = $foodGroup;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeImmutable $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getSatisfiedOn(): ?\DateTimeImmutable
    {
        return $this->satisfiedOn;
    }

    public function setSatisfiedOn(?\DateTimeImmutable $satisfiedOn): self
    {
        $this->satisfiedOn = $satisfiedOn;

        return $this;
    }

    public function isSatisfied(): bool
    {
        return $this->satisfiedOn !== null;
    }
}
