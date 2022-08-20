<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Pet;
use App\Repository\PetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChooseAPetController extends PoppySeedPetsItemController
{
    public function getPet(Request $request, PetRepository $petRepository): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new NotFoundHttpException('There is no such pet.');

        return $pet;
    }
}