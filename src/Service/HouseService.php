<?php
namespace App\Service;

use App\Entity\User;

class HouseService
{
    private $petService;

    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    public function run(User $user)
    {
        foreach($user->getPets() as $pet)
        {
            if($pet->getTime() >= 60)
                $this->petService->runHour($pet);
        }
    }
}