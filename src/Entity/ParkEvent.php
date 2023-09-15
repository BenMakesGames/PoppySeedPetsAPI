<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ParkEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"parkEvent"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Pet::class)
     * @Groups({"parkEvent"})
     */
    private $participants;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"parkEvent"})
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     * @Groups({"parkEvent"})
     */
    private $results;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"parkEvent"})
     */
    private $date;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Pet[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipants($pets): self
    {
        foreach($pets as $pet)
            $this->addParticipant($pet);

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getResults(): ?string
    {
        return $this->results;
    }

    public function setResults(string $results): self
    {
        $this->results = $results;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }
}
