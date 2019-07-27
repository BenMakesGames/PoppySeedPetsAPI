<?php

namespace App\Entity;

use App\Enum\ParkEventTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParkEventRepository")
 */
class ParkEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Pet", inversedBy="parkEvents")
     */
    private $participants;

    /**
     * @ORM\Column(type="integer")
     */
    private $seats;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $ranOn;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $results;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ParkEventPrize", mappedBy="event", orphanRemoval=true)
     */
    private $prizes;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->prizes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if(!ParkEventTypeEnum::isAValue($type))
            throw new \InvalidArgumentException('"' . $type . '" is not a valid Park Event Type.');

        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Pet[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Pet $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Pet $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getRanOn(): ?\DateTimeImmutable
    {
        return $this->ranOn;
    }

    public function setRanOn(): self
    {
        $this->ranOn = new \DateTimeImmutable();

        return $this;
    }

    public function getResults(): ?string
    {
        return $this->results;
    }

    public function setResults(?string $results): self
    {
        $this->results = $results;

        return $this;
    }

    /**
     * @return Collection|ParkEventPrize[]
     */
    public function getPrizes(): Collection
    {
        return $this->prizes;
    }

    public function addPrize(ParkEventPrize $prize): self
    {
        if (!$this->prizes->contains($prize)) {
            $this->prizes[] = $prize;
            $prize->setEvent($this);
        }

        return $this;
    }

    public function removePrize(ParkEventPrize $prize): self
    {
        if ($this->prizes->contains($prize)) {
            $this->prizes->removeElement($prize);
            // set the owning side to null (unless already changed)
            if ($prize->getEvent() === $this) {
                $prize->setEvent(null);
            }
        }

        return $this;
    }
}
