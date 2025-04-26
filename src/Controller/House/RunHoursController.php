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


namespace App\Controller\House;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\HouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/house")]
class RunHoursController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/runHours", methods: ["POST"])]
    public function runHours(
        ResponseService $responseService, HouseService $houseService, EntityManagerInterface $em, LoggerInterface $logger,
        NormalizerInterface $normalizer,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        try
        {
            $houseService->run($user);
            $em->flush();
        }
        catch(\Doctrine\DBAL\Driver\PDO\Exception $e)
        {
            // hide serialization deadlocks from the end-user, in this case:
            if($e->getCode() === 1213)
                $logger->warning($e->getMessage(), [ 'trace' => $e->getTraceAsString() ]);
            else
                throw $e;
        }

        $data = [];

        if($responseService->getReloadInventory())
        {
            $responseService->setReloadInventory(false);

            $inventory = $em->getRepository(Inventory::class)->findBy([
                'owner' => $userAccessor->getUserOrThrow(),
                'location' => LocationEnum::HOME
            ]);

            $data['inventory'] = $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]);
        }

        if($responseService->getReloadPets())
        {
            $responseService->setReloadPets(false);

            $petsAtHome = $em->getRepository(Pet::class)->findBy([
                'owner' => $user->getId(),
                'location' => PetLocationEnum::HOME,
            ]);

            $data['pets'] = $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ]);
        }

        return $responseService->success($data);
    }
}
