<?php
declare(strict_types=1);

namespace App\Model\ParkEvent;

use App\Entity\Pet;

class KinBallTeam
{
    public string $color;

    /** @var KinBallParticipant[] */
    public array $pets = [];

    public int $wins = 0;

    public function __construct(string $color)
    {
        $this->color = $color;
    }
}