<?php

namespace App\Entity;

use App\Service\Squirrel3;
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
     * @ORM\Column(type="smallint")
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

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myGreenhouse"})
     */
    private $maxWaterPlants = 0;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"myGreenhouse"})
     */
    private $maxDarkPlants = 0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myGreenhouse"})
     */
    private $hasComposter = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $composterFood = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $composterBonusCountdown = 0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myGreenhouse"})
     */
    private $canUseBeeNetting = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myGreenhouse"})
     */
    private $hasBeeNetting = false;

    public function __construct()
    {
        $this->setComposterBonusCountdown();
    }

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

    public function getMaxWaterPlants(): ?int
    {
        return $this->maxWaterPlants;
    }

    public function increaseMaxWaterPlants(int $amount): self
    {
        $this->maxWaterPlants += $amount;

        return $this;
    }

    public function getMaxDarkPlants(): ?int
    {
        return $this->maxDarkPlants;
    }

    public function increaseMaxDarkPlants(int $amount): self
    {
        $this->maxDarkPlants += $amount;

        return $this;
    }

    public function getHasComposter(): ?bool
    {
        return $this->hasComposter;
    }

    public function setHasComposter(bool $hasComposter): self
    {
        $this->hasComposter = $hasComposter;

        return $this;
    }

    public function getComposterFood(): ?int
    {
        return $this->composterFood;
    }

    public function setComposterFood(int $composterFood): self
    {
        $this->composterFood = $composterFood;

        return $this;
    }

    public function getComposterBonusCountdown(): ?int
    {
        return $this->composterBonusCountdown;
    }

    public function setComposterBonusCountdown(): self
    {
        $squirrel3 = new Squirrel3();

        if($this->composterBonusCountdown <= 0)
            $this->composterBonusCountdown += $squirrel3->rngNextInt(3 * 20, 7 * 20);

        return $this;
    }

    public function decreaseComposterBonusCountdown(int $amount): self
    {
        $this->composterBonusCountdown -= $amount;

        return $this;
    }

    public function getCanUseBeeNetting(): ?bool
    {
        return $this->canUseBeeNetting;
    }

    public function setCanUseBeeNetting(bool $canUseBeeNetting): self
    {
        $this->canUseBeeNetting = $canUseBeeNetting;

        return $this;
    }

    public function getHasBeeNetting(): ?bool
    {
        return $this->hasBeeNetting;
    }

    public function setHasBeeNetting(bool $hasBeeNetting): self
    {
        $this->hasBeeNetting = $hasBeeNetting;

        return $this;
    }
}
