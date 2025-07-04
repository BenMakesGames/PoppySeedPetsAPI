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


namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\UserAccessor;
use App\Service\Filter\PetFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class GetController
{
    #[Route("", methods: ["GET"])]
    public function searchPets(
        Request $request, ResponseService $responseService, PetFilterService $petFilterService
    ): JsonResponse
    {
        return $responseService->success(
            $petFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_PUBLIC_PROFILE ]
        );
    }

    #[Route("/my", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyPets(
        ResponseService $responseService, ManagerRegistry $doctrine,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $petRepository = $doctrine->getRepository(Pet::class, 'readonly');

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        return $responseService->success($petsAtHome, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/my/{id}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyPet(
        ResponseService $responseService, EntityManagerInterface $em, UserAccessor $userAccessor,
        int $id
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $id,
            'owner' => $user->getId(),
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/{pet}", methods: ["GET"], requirements: ["pet" => "\d+"])]
    public function profile(Pet $pet, ResponseService $responseService): JsonResponse
    {
        return $responseService->success($pet, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
    }
}
