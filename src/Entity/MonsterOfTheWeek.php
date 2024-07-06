<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
    private int $communityTotal = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $easyPrize = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $mediumPrize = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $hardPrize = null;

    #[ORM\Column]
    private int $level = 1;

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

    public function getCommunityTotal(): int
    {
        return $this->communityTotal;
    }

    public function setCommunityTotal(int $communityTotal): static
    {
        $this->communityTotal = $communityTotal;

        return $this;
    }

    public function getEasyPrize(): Item
    {
        return $this->easyPrize;
    }

    public function setEasyPrize(Item $easyPrize): static
    {
        $this->easyPrize = $easyPrize;

        return $this;
    }

    public function getMediumPrize(): Item
    {
        return $this->mediumPrize;
    }

    public function setMediumPrize(Item $mediumPrize): static
    {
        $this->mediumPrize = $mediumPrize;

        return $this;
    }

    public function getHardPrize(): Item
    {
        return $this->hardPrize;
    }

    public function setHardPrize(Item $hardPrize): static
    {
        $this->hardPrize = $hardPrize;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }
}
