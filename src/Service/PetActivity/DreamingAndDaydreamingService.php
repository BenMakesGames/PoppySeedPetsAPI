<?php

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Model\ComputedPetSkills;
use App\Service\IRandom;
use App\Service\PetActivity\Daydreams\IceCreamDaydream;
use App\Service\PetActivity\Daydreams\PizzaDaydream;

class DreamingAndDaydreamingService
{
    private DreamingService $dreamingService;
    private IceCreamDaydream $iceCreamDaydream;
    private PizzaDaydream $pizzaDaydream;
    private IRandom $squirrel3;

    public function __construct(
        DreamingService $dreamingService, IceCreamDaydream $iceCreamDaydream, PizzaDaydream $pizzaDaydream,
        IRandom $squirrel3
    )
    {
        $this->dreamingService = $dreamingService;
        $this->iceCreamDaydream = $iceCreamDaydream;
        $this->pizzaDaydream = $pizzaDaydream;
        $this->squirrel3 = $squirrel3;
    }

    public function maybeDreamOrDaydream(ComputedPetSkills $petWithSkills): bool
    {
        $pet = $petWithSkills->getPet();

        if($this->maybeDreamDueToStatusEffect($petWithSkills))
            return true;

        if($this->toolOrMeritInducedDream($pet, $this->squirrel3))
        {
            $this->dreamingService->dream($pet);
            return true;
        }

        return false;
    }

    private static function toolOrMeritInducedDream(Pet $pet, IRandom $rng): bool
    {
        if($pet->hasMerit(MeritEnum::DREAMWALKER) && $rng->rngNextInt(1, 200) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->isDreamcatcher() && $rng->rngNextInt(1, 200) === 1)
            return true;

        return false;
    }

    private function maybeDreamDueToStatusEffect(ComputedPetSkills $petWithSkills): bool
    {
        if(!$this->squirrel3->rngNextBool())
            return false;

        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::ONEIRIC))
        {
            $this->dreamingService->dream($pet);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::ONEIRIC));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_ICE_CREAM))
        {
            $this->iceCreamDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_ICE_CREAM));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_PIZZA))
        {
            $this->pizzaDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_PIZZA));
            return true;
        }

        return false;
    }
}