<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GreenhouseRepository")
 */
class Greenhouse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="greenhouse", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myGreenhouse"})
     */
    private $maxPlants = 3;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myGreenhouse"})
     */
    private $hasBirdBath = false;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"myGreenhouse"})
     */
    private $visitingBird = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getMaxPlants(): int
    {
        return $this->maxPlants;
    }

    public function increaseMaxPlants(int $amount): self
    {
        $this->maxPlants += $amount;

        return $this;
    }

    public function getHasBirdBath(): bool
    {
        return $this->hasBirdBath;
    }

    public function setHasBirdBath(bool $hasBirdBath): self
    {
        $this->hasBirdBath = $hasBirdBath;

        return $this;
    }

    public function getVisitingBird(): ?string
    {
        return $this->visitingBird;
    }

    public function setVisitingBird(?string $visitingBird): self
    {
        $this->visitingBird = $visitingBird;

        return $this;
    }
}
