<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Index(name: 'badge_idx', columns: ['badge'])]
class PetBadge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'badges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pet $pet = null;

    #[ORM\Column(length: 40)]
    private ?string $badge = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAcquired = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): static
    {
        $this->pet = $pet;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getDateAcquired(): ?\DateTimeImmutable
    {
        return $this->dateAcquired;
    }

    public function setDateAcquired(\DateTimeImmutable $dateAcquired): static
    {
        $this->dateAcquired = $dateAcquired;

        return $this;
    }
}
