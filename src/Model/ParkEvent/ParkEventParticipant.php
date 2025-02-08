<?php
declare(strict_types=1);

namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Entity\PetActivityLog;

interface ParkEventParticipant
{
    public function getPet(): Pet;
    public function getIsWinner(): bool;
    public function getActivityLog(): PetActivityLog;
}