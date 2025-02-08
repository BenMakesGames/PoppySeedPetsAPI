<?php
declare(strict_types=1);

namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;

interface ParkEventInterface
{
    public function isGoodNumberOfPets(int $petCount): bool;
    public function play($pets): ParkEvent;
}