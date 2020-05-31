<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;

class JoustingTeam
{
    /** @var Pet */
    public $rider;

    /** @var Pet */
    public $mount;

    /** @var integer */
    public $wins = 0;

    /** @var JoustingTeam */
    public $defeatedBy;

    public function __construct(Pet $pet1, Pet $pet2)
    {
        $this->rider = $pet1;
        $this->mount = $pet2;
    }

    public function getTeamName(): string
    {
        return $this->rider->getName() . '/' . $this->mount->getName();
    }

    public function randomizeRoles()
    {
        if(mt_rand(1, 2) === 1)
            $this->switchRoles();
    }

    public function switchRoles()
    {
        $previousRider = $this->rider;
        $this->rider = $this->mount;
        $this->mount = $previousRider;
    }
}
