<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Enum\MeritEnum;

class CTFParticipant
{
    public const ROLE_DEFEND = 'defend';
    public const ROLE_PATROL = 'patrol';
    public const ROLE_EXPLORE = 'explore';

    /** @var Pet */ public $pet;
    /** @var CTFTeam */ public $team;
    /** @var string */ public $role;
    /** @var bool */ public $inJail;
    /** @var int */ public $skill;

    public function pickRole()
    {
        $skills = $this->pet->getSkills();
        $this->skill = floor($skills->getPerception() * 3 + $skills->getDexterity() * 1.5 + $skills->getStrength() + $skills->getStealth() / 2 + $skills->getBrawl() / 4);

        $attack = mt_rand(3, 10 + $this->pet->getStealth() + ($this->pet->hasMerit(MeritEnum::DARKVISION) ? 1 : 0));
        $defend = mt_rand(1, 10 + $skills->getBrawl());

        if($attack >= $defend)
            $this->role = self::ROLE_EXPLORE;
        else
            $this->role = $this->pet->getPerception() > $this->pet->getStrength() ? self::ROLE_DEFEND : self::ROLE_PATROL;
    }
}
