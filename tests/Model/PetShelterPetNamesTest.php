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
        $uniquePetNames = array_unique(PetShelterPet::PetNames);
        $duplicatePetNames = array_diff(PetShelterPet::PetNames, $uniquePetNames);

        self::assertEmpty(
            $duplicatePetNames,
            "The following pet names are not unique: " . implode(", ", $duplicatePetNames)
        );
    }

    public function testPetNamesAreInAlphabeticalOrder()
    {
        for($i = 0; $i < count(PetShelterPet::PetNames) - 1; $i++)
        {
            $petName = PetShelterPet::PetNames[$i];
            $nextPetName = PetShelterPet::PetNames[$i + 1];

            // if you know a better way to do this, please make this better :P
            $normalizedPetName = str_replace('\'', '', iconv('UTF-8', 'ASCII//TRANSLIT', $petName));
            $normalizedNextPetName = str_replace('\'', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nextPetName));

            self::assertTrue(
                $normalizedPetName <= $normalizedNextPetName,
                "The pet names are not in alphabetical order: \"{$petName}\" (\"{$normalizedPetName}\") should be after \"{$nextPetName}\" (\"{$normalizedNextPetName}\")."
            );
        }
    }
}