<?php
declare(strict_types=1);

namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Entity\PetActivityLog;

class KinBallParticipant implements ParkEventParticipant
{
    public Pet $pet;
    public int $skill;
    public int $team;
    public bool $isWinner = false;
    public PetActivityLog $activityLog;

    public function __construct(Pet $pet, int $team)
    {
        $this->pet = $pet;
        $this->team = $team;
        $this->skill = self::getSkill($pet);
    }

    public static function getSkill(Pet $pet): int
    {
        return (int)floor($pet->getSkills()->getDexterity() * 2.5 + $pet->getSkills()->getStrength() * 2 + $pet->getSkills()->getPerception() * 1.5);
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
