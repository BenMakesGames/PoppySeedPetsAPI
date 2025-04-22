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

    public function randomizeRoles(IRandom $rng): void
    {
        if($rng->rngNextBool())
            $this->switchRoles();
    }

    public function switchRoles(): void
    {
        $previousRider = $this->rider;
        $this->rider = $this->mount;
        $this->mount = $previousRider;
    }
}
