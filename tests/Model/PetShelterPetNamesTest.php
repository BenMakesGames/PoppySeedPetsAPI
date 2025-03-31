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


namespace Model;

use App\Model\PetShelterPet;
use PHPUnit\Framework\TestCase;

/**
 * JUSTIFICATION: All pet names should be in alphabetical order, to make it easy for a human
 * editor to tell if the name already exists. Also: duplicate names should not be added :P
 */
class PetShelterPetNamesTest extends TestCase
{
    public function testPetNamesAreUnique()
    {
        // TODO: it'd be nice to tell the user which name(s) are duplicates.
        $petNames = PetShelterPet::PetNames;

        $uniquePetNames = array_unique($petNames);

        self::assertCount(count($petNames), $uniquePetNames, "There are duplicate pet names.");
    }

    public function testPetNamesAreInAlphabeticalOrder()
    {
        $collator = \Collator::create('en_US');

        for($i = 0; $i < count(PetShelterPet::PetNames) - 1; $i++)
        {
            $petName = PetShelterPet::PetNames[$i];
            $nextPetName = PetShelterPet::PetNames[$i + 1];

            self::assertTrue(
                $collator->compare($petName, $nextPetName) < 0,
                "The pet names are not in alphabetical order: \"{$petName}\" should be after \"{$nextPetName}\"."
            );
        }
    }
}