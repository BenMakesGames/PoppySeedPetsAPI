<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GreenhousePlantRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="weeds_idx", columns={"weeds"})
 * })
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
     * @ORM\Column(type="integer")
     */
    private $weeds = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"greenhousePlant"})
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

    public function getGrowth(): ?int
    {
        return $this->growth;
    }

    public function setGrowth(int $growth): self
    {
        $this->growth = $growth;

        return $this;
    }

    public function getWeeds(): ?int
    {
        return $this->weeds;
    }

    public function setWeeds(int $weeds): self
    {
        $this->weeds = $weeds;

        return $this;
    }

    public function getLastInteraction(): ?\DateTimeImmutable
    {
        return $this->lastInteraction;
    }

    public function setLastInteraction(\DateTimeImmutable $lastInteraction): self
    {
        $this->lastInteraction = $lastInteraction;

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
            return round($this->growth / $this->getPlant()->getTimeToFruit(), 1);
        else
            return round($this->growth / $this->getPlant()->getTimeToAdult(), 1);
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
}
