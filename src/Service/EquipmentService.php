<?php
namespace App\Service;

use App\Entity\Pet;
use App\Enum\LocationEnum;

class EquipmentService
{
    public function __construct()
    {

    }

    public function unequipPet(Pet $pet)
    {
        if($pet->getTool() === null)
            return;

        $pet->getTool()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;
        $pet->setTool(null);
    }

    public function unhatPet(Pet $pet)
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