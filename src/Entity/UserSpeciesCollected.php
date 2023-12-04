<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'user_species_idx', columns: ['user_id', 'species_id'])]
#[ORM\Entity]
class UserSpeciesCollected
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\ManyToOne(targetEntity: PetSpecies::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $species;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $discoveredOn;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $petName;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $colorA;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $colorB;

    /**
     * @Groups({"zoologistCatalog"})
     */
    #[ORM\Column(type: 'smallint')]
    private $scale;

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

    public function getPetName(): ?string
    {
        return $this->petName;
    }

    public function setPetName(string $petName): self
    {
        $this->petName = $petName;

        return $this;
    }

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function setScale(int $scale): self
    {
        $this->scale = $scale;

        return $this;
    }
}
