<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParkEventPrizeRepository")
 */
class ParkEventPrize
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParkEvent", inversedBy="prizes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Inventory", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $prize;

    /**
     * @ORM\Column(type="integer")
     */
    private $place;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?ParkEvent
    {
        return $this->event;
    }

    public function setEvent(?ParkEvent $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getPrize(): ?Inventory
    {
        return $this->prize;
    }

    public function setPrize(Inventory $prize): self
    {
        $this->prize = $prize;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(int $place): self
    {
        $this->place = $place;

        return $this;
    }
}
