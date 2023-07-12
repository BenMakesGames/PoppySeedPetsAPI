<?php

namespace App\Controller\Item\ChooseAPet;

use App\Entity\Pet;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ChooseAPetController extends AbstractController
{
    public function getPet(Request $request, PetRepository $petRepository): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }
}