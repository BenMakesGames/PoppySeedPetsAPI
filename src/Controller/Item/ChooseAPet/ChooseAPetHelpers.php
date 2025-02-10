<?php
declare(strict_types=1);

namespace App\Controller\Item\ChooseAPet;

use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPPetNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ChooseAPetHelpers
{
    public static function getPet(Request $request, User $user, EntityManagerInterface $em): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }
}