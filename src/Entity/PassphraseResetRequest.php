<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PassphraseResetRequestRepository")
 */
class PassphraseResetRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="passphraseResetRequest", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $expiresOn;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getExpiresOn(): ?\DateTimeImmutable
    {
        return $this->expiresOn;
    }

    public function setExpiresOn(\DateTimeImmutable $expiresOn): self
    {
        $this->expiresOn = $expiresOn;

        return $this;
    }
}
