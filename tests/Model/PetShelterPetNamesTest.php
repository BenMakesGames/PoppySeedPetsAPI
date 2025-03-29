<?php

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