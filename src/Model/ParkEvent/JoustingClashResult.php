<?php
namespace App\Model\ParkEvent;

use App\Service\Squirrel3;

class JoustingClashResult
{
    /** @var JoustingTeam */ public $team1;
    /** @var JoustingTeam */ public $team2;

    public $rider1Hit = false;
    public $rider2Hit = false;

    public $rider1BrokeLance = false;
    public $rider1DismountedRider2 = false;
    public $rider1StumbledMount2 = false;
    public $rider2BrokeLance = false;
    public $rider2DismountedRider1 = false;
    public $rider2StumbledMount1 = false;
    public $boringClash = false;

    public function __construct(JoustingTeam $team1, JoustingTeam $team2)
    {
        $squirrel3 = new Squirrel3();

        $this->team1 = $team1;
        $this->team2 = $team2;

        $rider1Skills = $team1->rider->getSkills();
        $rider2Skills = $team2->rider->getSkills();

        $mount1Skills = $team1->mount->getSkills();
        $mount2Skills = $team2->mount->getSkills();

        $rider1Attack = $squirrel3->rngNextInt(1, 10 + $rider1Skills->getDexterity() + $rider1Skills->getBrawl());
        $rider1Dodge = $squirrel3->rngNextInt(1, 10 + $rider1Skills->getDexterity() + $rider1Skills->getBrawl());

        $rider2Attack = $squirrel3->rngNextInt(1, 10 + $rider2Skills->getDexterity() + $rider2Skills->getBrawl());
        $rider2Dodge = $squirrel3->rngNextInt(1, 10 + $rider2Skills->getDexterity() + $rider2Skills->getBrawl());

        $this->rider1Hit = $rider1Attack > $rider2Dodge;
        $this->rider2Hit = $rider2Attack > $rider1Dodge;

        $totalMountSpeed = $mount1Skills->getStrength() + $mount2Skills->getStrength();

        if($this->rider1Hit)
        {
            $rider1HitStrength = $squirrel3->rngNextInt(1, 10 + $rider1Skills->getStrength() + $totalMountSpeed);

            $this->rider1BrokeLance = $rider1HitStrength >= 8;

            $rider2BraceRoll = $squirrel3->rngNextInt(1, 10 + $rider2Skills->getStamina() * 2 + $rider2Skills->getDexterity());
            $mount2BraceRoll = $squirrel3->rngNextInt(1, 10 + $mount2Skills->getDexterity() * 2 + $mount2Skills->getStamina());

            $this->rider1DismountedRider2 = $rider1HitStrength > $rider2BraceRoll;
            $this->rider1StumbledMount2 = $rider1HitStrength > $mount2BraceRoll;
        }

        if($this->rider2Hit)
        {
            $rider2HitStrength = $squirrel3->rngNextInt(1, 10 + $rider2Skills->getStrength() + $totalMountSpeed);

            $this->rider2BrokeLance = $rider2HitStrength >= 8;

            $rider1BraceRoll = $squirrel3->rngNextInt(1, 10 + $rider1Skills->getStamina() * 2 + $rider1Skills->getDexterity());
            $mount1BraceRoll = $squirrel3->rngNextInt(1, 10 + $mount1Skills->getDexterity() * 2 + $mount1Skills->getStamina());

            $this->rider2DismountedRider1 = $rider2HitStrength > $rider1BraceRoll;
            $this->rider2StumbledMount1 = $rider2HitStrength > $mount1BraceRoll;
        }

        $this->boringClash =
            !$this->rider1BrokeLance && !$this->rider2BrokeLance &&
            !$this->rider1DismountedRider2 && !$this->rider1StumbledMount2 &&
            !$this->rider2DismountedRider1 && !$this->rider2StumbledMount1
        ;
    }
}
