<?php

namespace App\Entity;

use App\Repository\PetActivityLogPetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PetActivityLogPetRepository::class)]
class PetActivityLogPet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\ManyToOne(inversedBy: 'petActivityLogPets')]
    #[ORM\JoinColumn(nullable: false)]
    private PetActivityLog $activityLog;

    #[Groups(["petActivityLogAndPublicPet"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Pet $pet;

    #[Groups(["petActivityLogAndPublicPet"])]
    #[ORM\ManyToOne]
    private ?Item $equippedItem;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'object', nullable: true)]
    private ?object $changes = null;

    public function __construct(Pet $pet, PetActivityLog $activityLog)
    {
        $this->pet = $pet;
        $this->equippedItem = $pet->getTool()->getItem();
        $this->activityLog = $activityLog;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityLog(): PetActivityLog
    {
        return $this->activityLog;
    }

    public function setActivityLog(PetActivityLog $activityLog): static
    {
        $this->activityLog = $activityLog;

        return $this;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): static
    {
        $this->pet = $pet;

        return $this;
    }

    public function getEquippedItem(): ?Item
    {
        return $this->equippedItem;
    }

    public function setEquippedItem(?Item $equippedItem): static
    {
        $this->equippedItem = $equippedItem;

        return $this;
    }

    public function getChanges(): ?object
    {
        return $this->changes;
    }

    public function setChanges(?object $changes): static
    {
        $this->changes = $changes;

        return $this;
    }
    
    public function addInterestingness(int $interestingness): static
    {
        $this->activityLog->addInterestingness($interestingness);

        return $this;
    }
}
