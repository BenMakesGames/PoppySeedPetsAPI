<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Model\ParkEvent;

use App\Service\IRandom;

final class JoustingClashResult
{
    public JoustingTeam $team1;
    public JoustingTeam $team2;

    public bool $rider1Hit = false;
    public bool $rider2Hit = false;

    public bool $rider1BrokeLance = false;
    public bool $rider1DismountedRider2 = false;
    public bool $rider1StumbledMount2 = false;
    public bool $rider2BrokeLance = false;
    public bool $rider2DismountedRider1 = false;
    public bool $rider2StumbledMount1 = false;
    public bool $boringClash = false;

    public function __construct(IRandom $rng, JoustingTeam $team1, JoustingTeam $team2)
    {
        $this->team1 = $team1;
        $this->team2 = $team2;

        $rider1Skills = $team1->rider->getSkills();
        $rider2Skills = $team2->rider->getSkills();

        $mount1Skills = $team1->mount->getSkills();
        $mount2Skills = $team2->mount->getSkills();

        $rider1Attack = $rng->rngNextInt(1, 10 + $rider1Skills->getDexterity() + $rider1Skills->getBrawl());
        $rider1Dodge = $rng->rngNextInt(1, 10 + $rider1Skills->getDexterity() + $rider1Skills->getBrawl());

        $rider2Attack = $rng->rngNextInt(1, 10 + $rider2Skills->getDexterity() + $rider2Skills->getBrawl());
        $rider2Dodge = $rng->rngNextInt(1, 10 + $rider2Skills->getDexterity() + $rider2Skills->getBrawl());

        $this->rider1Hit = $rider1Attack > $rider2Dodge;
        $this->rider2Hit = $rider2Attack > $rider1Dodge;

        $totalMountSpeed = $mount1Skills->getStrength() + $mount2Skills->getStrength();

        if($this->rider1Hit)
        {
            $rider1HitStrength = $rng->rngNextInt(1, 10 + $rider1Skills->getStrength() + $totalMountSpeed);

            $this->rider1BrokeLance = $rider1HitStrength >= 8;

            $rider2BraceRoll = $rng->rngNextInt(1, 10 + $rider2Skills->getStamina() * 2 + $rider2Skills->getDexterity());
            $mount2BraceRoll = $rng->rngNextInt(1, 10 + $mount2Skills->getDexterity() * 2 + $mount2Skills->getStamina());

            $this->rider1DismountedRider2 = $rider1HitStrength > $rider2BraceRoll;
            $this->rider1StumbledMount2 = $rider1HitStrength > $mount2BraceRoll;
        }

        if($this->rider2Hit)
        {
            $rider2HitStrength = $rng->rngNextInt(1, 10 + $rider2Skills->getStrength() + $totalMountSpeed);

            $this->rider2BrokeLance = $rider2HitStrength >= 8;

            $rider1BraceRoll = $rng->rngNextInt(1, 10 + $rider1Skills->getStamina() * 2 + $rider1Skills->getDexterity());
            $mount1BraceRoll = $rng->rngNextInt(1, 10 + $mount1Skills->getDexterity() * 2 + $mount1Skills->getStamina());

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
