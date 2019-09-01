<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserSessionRepository")
 */
class UserSession
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userSessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $sessionId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $sessionExpiration;

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

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSessionExpiration(): ?\DateTimeImmutable
    {
        return $this->sessionExpiration;
    }

    public function setSessionExpiration(?int $sessionHours = null): self
    {
        if(!$sessionHours)
            $sessionHours = $this->getUser()->getDefaultSessionLengthInHours();

        $this->sessionExpiration = (new \DateTimeImmutable())->modify('+' . $sessionHours . ' hours');

        return $this;
    }
}
