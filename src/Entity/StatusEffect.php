<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\StatusEffectEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'time_remaining_idx', columns: ['time_remaining'])]
#[ORM\Entity(repositoryClass: 'App\Repository\StatusEffectRepository')]
class StatusEffect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Pet::class, inversedBy: 'statusEffects')]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    #[ORM\Column(type: 'string', length: 40)]
    #[Groups(['myPet'])]
    private $status;

    #[ORM\Column(type: 'integer')]
    private $totalDuration = 0;

    #[ORM\Column(type: 'integer')]
    private $timeRemaining = 0;

    #[ORM\Column(type: 'integer')]
    private $counter = 0;

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

    public function getStatus(): string
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

    public function getTotalDuration(): int
    {
        return $this->totalDuration;
    }

    public function setTotalDuration(int $totalDuration): self
    {
        $this->totalDuration = $totalDuration;

        return $this;
    }

    public function getTimeRemaining(): int
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
        if(in_array($this->status, [
            StatusEffectEnum::BUBBLEGUMD,
            StatusEffectEnum::OIL_COVERED,

            StatusEffectEnum::BITTEN_BY_A_VAMPIRE,
            StatusEffectEnum::BITTEN_BY_A_WERECREATURE,
            StatusEffectEnum::WEREFORM,

            StatusEffectEnum::FOCUSED_NATURE,
            StatusEffectEnum::FOCUSED_SCIENCE,
            StatusEffectEnum::FOCUSED_STEALTH,
            StatusEffectEnum::FOCUSED_ARCANA,
            StatusEffectEnum::FOCUSED_CRAFTS,
            StatusEffectEnum::FOCUSED_BRAWL,
            StatusEffectEnum::FOCUSED_MUSIC,

            StatusEffectEnum::DAYDREAM_ICE_CREAM,
            StatusEffectEnum::DAYDREAM_PIZZA,
            StatusEffectEnum::DAYDREAM_FOOD_FIGHT,
            StatusEffectEnum::DAYDREAM_NOODLES,

            StatusEffectEnum::FATED_DELICIOUSNESS,
            StatusEffectEnum::FATED_SOAKEDLY,
            StatusEffectEnum::FATED_ELECTRICALLY,
            StatusEffectEnum::FATED_FERALLY,
            StatusEffectEnum::FATED_LUNARLY,
            StatusEffectEnum::FATED_CHEESEWARDLY,

            StatusEffectEnum::LAPINE_WHISPERS,
            StatusEffectEnum::ONEIRIC,
            StatusEffectEnum::GOBBLE_GOBBLE,
        ]))
        {
            return $this;
        }

        $this->timeRemaining -= $time;

        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function incrementCounter(): self
    {
        $this->counter++;

        return $this;
    }
}
