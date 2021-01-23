<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Service\Squirrel3;

class CTFTeam
{
    public $color;
    /** @var int */ public $points;
    /** @var int */ public $flagDifficulty;
    /** @var CTFParticipant[] */ public $members;

    function __construct($color)
    {
        $this->color = $color;
    }

    public function addParticipant(Pet $pet, Squirrel3 $squirrel3): CTFParticipant
    {
        $p = new CTFParticipant();
        $p->pet = $pet;
        $p->team = $this;
        $p->inJail = false;
        $p->pickRole($squirrel3);

        return $p;
    }
}
