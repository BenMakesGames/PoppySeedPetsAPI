<?php
namespace App\Functions;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Model\ComputedPetSkills;

final class ActivityHelpers
{
    public static function SourceOfLight(ComputedPetSkills $petWithSkills)
    {
        if($petWithSkills->getPet()->hasMerit(MeritEnum::DARKVISION))
            return 'Darkvision';

        if($petWithSkills->getPet()->getTool() && $petWithSkills->getPet()->getTool()->providesLight())
            return $petWithSkills->getPet()->getTool()->getFullItemName();

        throw new \Exception('No light source found! (Bad game logic!)');
    }

    public static function PetName(Pet $pet): string
    {
        return '%pet:' . $pet->getId() . '.name%';
    }

    public static function UserName(User $user, bool $capitalize = false): string
    {
        if($capitalize)
            return '%user:' . $user->getId() . '.Name%';
        else
            return '%user:' . $user->getId() . '.name%';
    }

    public static function UserNamePossessive(User $user, bool $capitalize = false): string
    {
        if($capitalize)
            return '%user:' . $user->getId() . '.Name\'s%';
        else
            return '%user:' . $user->getId() . '.name\'s%';
    }
}