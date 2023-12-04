<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\TraderRepository')]
class Trader
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Groups({"traderOffer"})
     */
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    /**
     * @Groups({"traderOffer"})
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $colorA;

    /**
     * @Groups({"traderOffer"})
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $colorB;

    /**
     * @Groups({"traderOffer"})
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $colorC;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }

    public function getColorC(): ?string
    {
        return $this->colorC;
    }

    public function setColorC(string $colorC): self
    {
        $this->colorC = $colorC;

        return $this;
    }
}
