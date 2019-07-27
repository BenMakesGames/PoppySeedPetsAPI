<?php
namespace App\Model\ParkEvent;

use App\Entity\Pet;

class KinBallTeam
{
    /** @var string */
    public $color;

    /** @var KinBallParticipant[] */
    public $pets = [];

    /** @var integer  */
    public $wins = 0;

    public function __construct(string $color)
    {
        $this->color = $color;
    }
}