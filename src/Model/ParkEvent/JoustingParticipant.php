<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Entity\PetActivityLog;

class JoustingParticipant implements ParkEventParticipant
{
    private Pet $pet;

    public bool $isWinner;
    public PetActivityLog $activityLog;

    public function __construct(Pet $pet)
    {
        $this->pet = $pet;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function getIsWinner(): bool
    {
        return $this->isWinner;
    }

    public function getActivityLog(): PetActivityLog
    {
        return $this->activityLog;
    }
}