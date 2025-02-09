<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Service\IRandom;

class AdventureMath
{
    public static function petAttractsBug(IRandom $rng, Pet $pet, int $oneInXChance)
    {
        if($pet->hasMerit(MeritEnum::LUMINARY_ESSENCE))
            $oneInXChance = (int)\ceil($oneInXChance * 2 / 3);

        return $oneInXChance <= 1 || $rng->rngNextInt(1, $oneInXChance) == 1;
    }
}