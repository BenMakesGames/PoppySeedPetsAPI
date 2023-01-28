<?php
namespace App\Functions;

use App\Entity\Pet;
use App\Enum\LocationEnum;

class EquipmentFunctions
{
    public static function unequipPet(Pet $pet)
    {
        if($pet->getTool() === null)
            return;

        $pet->getTool()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;
        $pet->setTool(null);
    }

    public static function unhatPet(Pet $pet)
    {
        if($pet->getHat() === null)
            return;

        $pet->getHat()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;
        $pet->setHat(null);
    }
}