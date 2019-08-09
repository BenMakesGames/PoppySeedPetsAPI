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
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"greenhousePlant"})
     */
    private $plant;

    /**
     * @ORM\Column(type="integer")
     */
    private $growth;

    /**
     * @ORM\Column(type="integer")
     */
    private $weeds;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlant(): ?Item
    {
        return $this->plant;
    }

    public function setPlant(?Item $plant): self
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
}
