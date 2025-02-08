<?php
declare(strict_types=1);

namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Service\IRandom;

class JoustingTeam
{
    public Pet $rider;
    public Pet $mount;
    public int $wins = 0;
    public JoustingTeam $defeatedBy;

    public function __construct(Pet $pet1, Pet $pet2)
    {
        $this->rider = $pet1;
        $this->mount = $pet2;
    }

    public function getTeamName(): string
    {
        return $this->rider->getName() . '/' . $this->mount->getName();
    }

    public function randomizeRoles(IRandom $squirrel3)
    {
        if($squirrel3->rngNextBool())
            $this->switchRoles();
    }

    public function switchRoles()
    {
        $previousRider = $this->rider;
        $this->rider = $this->mount;
        $this->mount = $previousRider;
    }
}
