<?php

namespace App\Controller\Item\ChooseAPet;

use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class ChooseAPetHelpers
{
    public static function getPet(Request $request, User $user, PetRepository $petRepository): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }
}