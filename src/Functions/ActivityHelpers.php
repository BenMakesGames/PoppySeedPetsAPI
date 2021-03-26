<?php
namespace App\Functions;

use App\Entity\Pet;
use App\Entity\User;

final class ActivityHelpers
{
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

    public static function UserNamePosessive(User $user, bool $capitalize = false): string
    {
        if($capitalize)
            return '%user:' . $user->getId() . '.Name\'s%';
        else
            return '%user:' . $user->getId() . '.name\'s%';
    }
}