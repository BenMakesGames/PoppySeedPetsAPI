<?php

namespace App\Entity;

use App\Repository\UserMonthlyStoryAdventureStepCompletedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserMonthlyStoryAdventureStepCompletedRepository::class)]
class UserMonthlyStoryAdventureStepCompleted
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups([ "starKindredStoryStepComplete" ])]
    #[ORM\ManyToOne(targetEntity: MonthlyStoryAdventureStep::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $adventureStep;

    #[Groups([ "starKindredStoryStepComplete" ])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $completedOn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAdventureStep(): ?MonthlyStoryAdventureStep
    {
        return $this->adventureStep;
    }

    public function setAdventureStep(?MonthlyStoryAdventureStep $adventureStep): self
    {
        $this->adventureStep = $adventureStep;

        return $this;
    }

    public function getCompletedOn(): ?\DateTimeImmutable
    {
        return $this->completedOn;
    }

    public function setCompletedOn(\DateTimeImmutable $completedOn): self
    {
        $this->completedOn = $completedOn;

        return $this;
    }
}
