<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PushSubscriptionRepository")
 */
class PushSubscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"notificationPreferences"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pushSubscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $endpoint;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $p256dh;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $auth;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"notificationPreferences"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"notificationPreferences"})
     */
    private $name = '';

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
    }

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

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getP256dh(): ?string
    {
        return $this->p256dh;
    }

    public function setP256dh(string $p256dh): self
    {
        $this->p256dh = $p256dh;

        return $this;
    }

    public function getAuth(): ?string
    {
        return $this->auth;
    }

    public function setAuth(string $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
