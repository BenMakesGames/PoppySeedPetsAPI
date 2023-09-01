<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserSubscriptionRepository::class)
 */
class UserSubscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="subscription", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myAccount"})
     */
    private $monthlyAmountInCents;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedOn;

    /**
     * @ORM\Column(type="integer")
     */
    private $patreonUserId;

    public function __construct()
    {
        $this->updatedOn = new \DateTimeImmutable();
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

    public function getMonthlyAmountInCents(): ?int
    {
        return $this->monthlyAmountInCents;
    }

    public function setMonthlyAmountInCents(int $monthlyAmountInCents): self
    {
        $this->monthlyAmountInCents = $monthlyAmountInCents;

        return $this;
    }

    public function getUpdatedOn(): ?\DateTimeImmutable
    {
        return $this->updatedOn;
    }

    public function setUpdatedOn(): self
    {
        $this->updatedOn = new \DateTimeImmutable();

        return $this;
    }

    public function getPatreonUserId(): int
    {
        return $this->patreonId;
    }

    public function setPatreonUserId(int $patreonId): self
    {
        $this->patreonId = $patreonId;

        return $this;
    }
}
