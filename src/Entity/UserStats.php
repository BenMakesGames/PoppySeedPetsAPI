<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserStatsRepository")
 */
class UserStats
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="stats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $stat;

    /**
     * @ORM\Column(type="integer")
     */
    private $value = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $firstTime;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastTime;

    public function __construct()
    {
        $this->firstTime = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStat(): ?string
    {
        return $this->stat;
    }

    public function setStat(string $stat): self
    {
        $this->stat = $stat;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function increaseValue(int $value): self
    {
        $this->lastTime = new \DateTimeImmutable();
        $this->value += $value;

        return $this;
    }

    public function getFirstTime(): ?\DateTimeImmutable
    {
        return $this->firstTime;
    }

    public function getLastTime(): ?\DateTimeImmutable
    {
        return $this->lastTime;
    }
}
