<?php

namespace App\Service\PetActivity;

use App\Model\ComputedPetSkills;
use App\Service\IRandom;

class KappaService
{
    private IRandom $rng;

    public function __construct(IRandom $rng)
    {
        $this->rng = $rng;
    }

    public function doHuntKappa(ComputedPetSkills $petWithSkills)
    {
        $totalSkill =
            $petWithSkills->getBrawl(false)->getTotal() +
            $petWithSkills->getStrength()->getTotal() +
            $petWithSkills->getDexterity()->getTotal();

        if($totalSkill >= 12 || $this->rng->rngNextInt(1, 20 + $totalSkill) >= 16)
        {

        }
        else
        {

        }
    }

    public function doReturnShirikodama(ComputedPetSkills $petWithSkills)
    {

    }
}