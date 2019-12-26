<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BeehiveRepository")
 */
class Beehive
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="beehive", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $workers;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $queenName;

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

    public function getWorkers(): ?int
    {
        return $this->workers;
    }

    public function setWorkers(int $workers): self
    {
        $this->workers = $workers;

        return $this;
    }

    public function getQueenName(): ?string
    {
        return $this->queenName;
    }

    public function setQueenName(string $queenName): self
    {
        $this->queenName = $queenName;

        return $this;
    }
}
