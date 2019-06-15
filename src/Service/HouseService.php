<?php
namespace App\Service;

use App\Entity\User;

class HouseService
{
    public function __construct()
    {
    }

    public function run(User $user)
    {
        foreach($user->getPets() as $pet)
        {
            if($pet->getEnergy() >= 60)
            {
                $pet->setFood($pet->getFood() - 1);
                // TODO: run this pet
            }
        }
    }
}