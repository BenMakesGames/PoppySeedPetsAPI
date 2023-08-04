<?php

namespace App\Entity;

use App\Repository\UserSpeciesCollectedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserSpeciesCollectedRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_species_idx", columns={"user_id", "species_id"})
 *     }
 * )
 */
class UserSpeciesCollected
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=PetSpecies::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $species;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $discoveredOn;

    public function __construct()
    {
        $this->discoveredOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSpecies(): PetSpecies
    {
        return $this->species;
    }

    public function setSpecies(PetSpecies $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function getDiscoveredOn(): \DateTimeImmutable
    {
        return $this->discoveredOn;
    }
}
