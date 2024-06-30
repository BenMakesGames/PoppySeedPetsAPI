<?php

namespace App\Entity;

use App\Repository\MonsterOfTheWeekContributionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonsterOfTheWeekContributionRepository::class)]
class MonsterOfTheWeekContribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MonsterOfTheWeek $monsterOfTheWeek = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedOn = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $rewardsClaimed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonsterOfTheWeek(): ?MonsterOfTheWeek
    {
        return $this->monsterOfTheWeek;
    }

    public function setMonsterOfTheWeek(?MonsterOfTheWeek $monsterOfTheWeek): static
    {
        $this->monsterOfTheWeek = $monsterOfTheWeek;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getModifiedOn(): ?\DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    public function setModifiedOn(\DateTimeImmutable $modifiedOn): static
    {
        $this->modifiedOn = $modifiedOn;

        return $this;
    }

    public function getRewardsClaimed(): ?int
    {
        return $this->rewardsClaimed;
    }

    public function setRewardsClaimed(int $rewardsClaimed): static
    {
        $this->rewardsClaimed = $rewardsClaimed;

        return $this;
    }
}
