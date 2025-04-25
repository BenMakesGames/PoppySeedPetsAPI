<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\PetShelter;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Functions\PetRepository;
use App\Functions\UserQuestRepository;
use App\Service\AdoptionService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/petShelter")]
class GetController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAvailablePets(
        AdoptionService $adoptionService, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $now = (new \DateTimeImmutable())->format('Y-m-d');
        $costToAdopt = $adoptionService->getAdoptionFee($user);
        $lastAdopted = UserQuestRepository::find($em, $user, 'Last Adopted a Pet');

        if($lastAdopted && $lastAdopted->getValue() === $now)
        {
            return $responseService->success([
                'costToAdopt' => $costToAdopt,
                'pets' => [],
                'dialog' => 'To make sure there are enough pets for everyone, we ask that you not adopt more than one pet per day.'
            ]);
        }

        [$pets, $dialog] = $adoptionService->getDailyPets($user);

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $dialog .= "no one catches your eye today, come back tomorrow. We get newcomers every day!\n\nSince you have so many pets in your house already, a pet you adopt will be placed into Daycare.";
        else
            $dialog .= "no one catches your eye today, come back tomorrow. We get newcomers every day!";

        $data = [
            'dialog' => $dialog,
            'pets' => $pets,
            'costToAdopt' => $costToAdopt,
            'petsAtHome' => $numberOfPetsAtHome,
            'maxPets' => $user->getMaxPets(),
        ];

        return $responseService->success(
            $data,
            [ SerializationGroupEnum::PET_SHELTER_PET ]
        );
    }
}
