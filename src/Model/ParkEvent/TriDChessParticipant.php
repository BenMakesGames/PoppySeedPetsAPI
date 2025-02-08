<?php
declare(strict_types=1);

namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;

class TriDChessParticipant implements ParkEventParticipant
{
    public Pet $pet;
    public int $skill;
    public bool $isWinner = false;
    public PetActivityLog $activityLog;

    public function __construct(Pet $pet)
    {
        $this->pet = $pet;
        $this->skill = self::getSkill($pet);
    }

    public static function getSkill(Pet $pet)
    {
        $skill = 1 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getScience();

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            $skill += 2;

        return $skill;
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
