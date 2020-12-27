<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Enum\MeritEnum;

class TriDChessParticipant
{
    /** @var Pet */
    public $pet;

    /** @var integer */
    public $skill;

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
}
