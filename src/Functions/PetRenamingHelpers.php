<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Functions;

use App\Entity\Pet;
use App\Entity\SpiritCompanion;
use App\Enum\MeritEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

final class PetRenamingHelpers
{
    public static function renamePet(EntityManagerInterface $em, Pet $pet, string $newName): void
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException('This pet is Affectionless. It\'s not interested in taking on a new name.');

        $petName = ProfanityFilterFunctions::filter(trim($newName));

        if($petName === $pet->getName())
            throw new PSPFormValidationException('That\'s the pet\'s current name!');

        if(\mb_strlen($petName) < 1 || \mb_strlen($petName) > 30)
            throw new PSPFormValidationException('Pet name must be between 1 and 30 characters long.');

        PetActivityLogFactory::createUnreadLog($em, $pet, "{$pet->getName()} has been renamed to {$petName}!");

        $pet->setName($petName);
    }

    public static function renameSpiritCompanion(EntityManagerInterface $em, SpiritCompanion $spiritCompanion, string $newName): void
    {
        $companionName = ProfanityFilterFunctions::filter(trim($newName));

        if($companionName === $spiritCompanion->getName())
            throw new PSPFormValidationException('That\'s the spirit companion\'s current name!');

        if(\mb_strlen($companionName) < 1 || \mb_strlen($companionName) > 30)
            throw new PSPFormValidationException('Spirit companion names must be between 1 and 30 characters long.');

        PetActivityLogFactory::createUnreadLog($em, $spiritCompanion->getPet(), ActivityHelpers::PetName($spiritCompanion->getPet()) . "'s spirit companion has been renamed to {$companionName}!");

        $spiritCompanion->setName($companionName);
    }
}