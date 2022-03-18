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
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="greenhouse", cascade={"persist", "remove"})
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
     * @ORM\OneToOne(targetEntity=Pet::class, cascade={"persist", "remove"})
     * @Groups({"helperPet"})
     */
    private $helper;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $butterfliesDismissedOn;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $beesDismissedOn;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $bees2DismissedOn;

    public function __construct()
    {
        $this->setComposterBonusCountdown();
        $this->butterfliesDismissedOn = new \DateTimeImmutable();
        $this->beesDismissedOn = new \DateTimeImmutable();
        $this->bees2DismissedOn = new \DateTimeImmutable();
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

    public function getHelper(): ?Pet
    {
        return $this->helper;
    }

    public function setHelper(?Pet $helper): self
    {
        $this->helper = $helper;

        return $this;
    }

    public function getButterfliesDismissedOn(): ?\DateTimeImmutable
    {
        return $this->butterfliesDismissedOn;
    }

    public function setButterfliesDismissedOn(\DateTimeImmutable $butterfliesDismissedOn): self
    {
        $this->butterfliesDismissedOn = $butterfliesDismissedOn;

        return $this;
    }

    public function getBeesDismissedOn(): ?\DateTimeImmutable
    {
        return $this->beesDismissedOn;
    }

    public function setBeesDismissedOn(\DateTimeImmutable $beesDismissedOn): self
    {
        $this->beesDismissedOn = $beesDismissedOn;

        return $this;
    }

    public function getBees2DismissedOn(): ?\DateTimeImmutable
    {
        return $this->bees2DismissedOn;
    }

    public function setBees2DismissedOn(\DateTimeImmutable $bees2DismissedOn): self
    {
        $this->bees2DismissedOn = $bees2DismissedOn;

        return $this;
    }
}
