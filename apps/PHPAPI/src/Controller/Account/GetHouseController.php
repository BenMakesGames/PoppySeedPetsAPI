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


namespace App\Controller\Account;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/account")]
class GetHouseController
{
    #[Route("/myHouse", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getHouse(
        ManagerRegistry $doctrine, ResponseService $responseService,
        NormalizerInterface $normalizer, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $petRepository = $doctrine->getRepository(Pet::class, 'readonly');
        $inventoryRepository = $doctrine->getRepository(Inventory::class, 'readonly');

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success([
            'inventory' => $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'pets' => $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ])
        ]);
    }
}
