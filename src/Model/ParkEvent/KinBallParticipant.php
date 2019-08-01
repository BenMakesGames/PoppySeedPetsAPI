<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;

class KinBallParticipant
{
    /** @var Pet */
    public $pet;

    /** @var integer */
    public $skill;

    /** @var integer */
    public $team;

    public function __construct(Pet $pet, int $team)
    {
        $this->pet = $pet;
        $this->team = $team;
        $this->skill = floor($pet->getSkills()->getDexterity() * 2.5 + $pet->getSkills()->getStrength() * 2 + $pet->getSkills()->getPerception() * 1.5);
    }
}