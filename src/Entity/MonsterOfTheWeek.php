<?php

namespace App\Entity;

use App\Repository\MonsterOfTheWeekRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonsterOfTheWeekRepository::class)]
class MonsterOfTheWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $monster = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private ?int $communityTotal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonster(): ?string
    {
        return $this->monster;
    }

    public function setMonster(string $monster): static
    {
        $this->monster = $monster;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCommunityTotal(): ?int
    {
        return $this->communityTotal;
    }

    public function setCommunityTotal(int $communityTotal): static
    {
        $this->communityTotal = $communityTotal;

        return $this;
    }
}
