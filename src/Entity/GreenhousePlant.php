<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GreenhousePlantRepository")
 */
class GreenhousePlant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"greenhousePlant"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemPlant")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"greenhousePlant"})
     */
    private $plant;

    /**
     * @ORM\Column(type="integer")
     */
    private $growth = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastInteraction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="greenhousePlants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"greenhousePlant"})
     */
    private $isAdult = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $previousGrowth = 0;

    public function __construct()
    {
        $this->lastInteraction = (new \DateTimeImmutable())->modify('-1 day');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlant(): ?ItemPlant
    {
        return $this->plant;
    }

    public function setPlant(ItemPlant $plant): self
    {
        $this->plant = $plant;

        return $this;
    }

    public function getGrowth(): int
    {
        return $this->growth;
    }

    public function clearGrowth(): self
    {
        $this->previousGrowth = 0;
        $this->growth = 0;

        return $this;
    }

    public function increaseGrowth(int $growth): self
    {
        $this->previousGrowth = $this->growth;

        $this->growth += $growth;

        if(!$this->getIsAdult())
        {
            if($this->growth >= $this->getPlant()->getTimeToAdult())
            {
                $this->previousGrowth = 0;
                $this->growth -= $this->getPlant()->getTimeToAdult();
                $this->setIsAdult(true);
            }
        }

        if($this->getIsAdult() && $this->growth >= $this->getPlant()->getTimeToFruit())
            $this->growth = $this->getPlant()->getTimeToFruit();

        $this->setLastInteraction();

        return $this;
    }

    /**
     * @Groups({"greenhousePlant"})
     */
    public function getCanNextInteract(): \DateTimeImmutable
    {
        return $this->getLastInteraction()->modify('+12 hours');
    }

    public function getLastInteraction(): \DateTimeImmutable
    {
        return $this->lastInteraction;
    }

    public function setLastInteraction(): self
    {
        $this->lastInteraction = new \DateTimeImmutable();

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getIsAdult(): ?bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(bool $isAdult): self
    {
        $this->isAdult = $isAdult;

        return $this;
    }

    /**
     * @Groups({"greenhousePlant"})
     */
    public function getProgress(): float
    {
        if($this->isAdult)
            return round($this->growth / $this->getPlant()->getTimeToFruit(), 2);
        else
            return round($this->growth / $this->getPlant()->getTimeToAdult(), 2);
    }

    /**
     * @Groups({"greenhousePlant"})
     */
    public function getPreviousProgress(): float
    {
        if($this->isAdult)
            return round($this->previousGrowth / $this->getPlant()->getTimeToFruit(), 1);
        else
            return round($this->previousGrowth / $this->getPlant()->getTimeToAdult(), 1);
    }

    /**
     * @Groups({"greenhousePlant"})
     */
    public function getImage()
    {
        if($this->isAdult)
        {
            if($this->getProgress() >= 1)
                return $this->getPlant()->getHarvestableImage();
            else
                return $this->getPlant()->getAdultImage();
        }
        else
        {
            if($this->getProgress() >= 0.5)
                return $this->getPlant()->getMediumImage();
            else
                return $this->getPlant()->getSproutImage();
        }
    }

    public function getPreviousGrowth(): int
    {
        return $this->previousGrowth;
    }
}
