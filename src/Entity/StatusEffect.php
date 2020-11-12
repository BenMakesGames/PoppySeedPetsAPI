<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\StatusEffectEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatusEffectRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="time_remaining_idx", columns={"time_remaining"}),
 * })
 */
class StatusEffect
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="statusEffects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalDuration;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeRemaining;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if(!StatusEffectEnum::isAValue($status))
            throw new EnumInvalidValueException(StatusEffectEnum::class, $status);

        $this->status = $status;

        return $this;
    }

    public function getTotalDuration(): ?int
    {
        return $this->totalDuration;
    }

    public function setTotalDuration(int $totalDuration): self
    {
        $this->totalDuration = $totalDuration;

        return $this;
    }

    public function getTimeRemaining(): ?int
    {
        return $this->timeRemaining;
    }

    public function setTimeRemaining(int $timeRemaining): self
    {
        $this->timeRemaining = $timeRemaining;

        return $this;
    }

    public function spendTime(int $time): self
    {
        // these status effects don't go away unless/until something SPECIAL happens:
        if($this->status === StatusEffectEnum::ONEIRIC || $this->status === StatusEffectEnum::GOBBLE_GOBBLE)
            return $this;

        $this->timeRemaining -= $time;

        return $this;
    }
}
