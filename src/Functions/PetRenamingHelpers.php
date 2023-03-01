<?php

namespace App\Functions;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Service\ResponseService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class PetRenamingHelpers
{
    public static function renamePet(ResponseService $responseService, Pet $pet, string $newName)
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new UnprocessableEntityHttpException('This pet is Affectionless. It\'s not interested in taking on a new name.');

        $petName = ProfanityFilterFunctions::filter(trim($newName));

        if($petName === $pet->getName())
            throw new UnprocessableEntityHttpException('That\'s the pet\'s current name!');

        if(\mb_strlen($petName) < 1 || \mb_strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        $responseService->createActivityLog($pet, "{$pet->getName()} has been renamed to {$petName}!", '');

        $pet->setName($petName);
    }
}