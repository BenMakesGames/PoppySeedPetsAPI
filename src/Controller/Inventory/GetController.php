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

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Service\Filter\InventoryFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/inventory")]
class GetController
{
    #[Route("/my", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyHouseInventory(
        ResponseService $responseService, ManagerRegistry $doctrine,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $inventoryRepository = $doctrine->getRepository(Inventory::class, 'readonly');

        $inventory = $inventoryRepository->findBy([
            'owner' => $userAccessor->getUserOrThrow(),
            'location' => LocationEnum::Home
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/my/{location}", methods: ["GET"], requirements: ["location" => "\d+"])]
    public function getMyInventory(
        Request $request, ResponseService $responseService, InventoryFilterService $inventoryFilterService,
        int $location,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        if(!LocationEnum::isAValue($location))
            throw new PSPFormValidationException('Invalid location given.');

        $user = $userAccessor->getUserOrThrow();

        $inventoryFilterService->addRequiredFilter('user', $user->getId());
        $inventoryFilterService->addRequiredFilter('location', $location);

        $inventoryFilterService->setUser($user);

        $inventory = $inventoryFilterService->getResults($request->query);

        return $responseService->success($inventory, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[Route("/summary/{location}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSummary(
        int $location, ResponseService $responseService, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $summary = $inventoryService->getInventoryQuantities($user, $location);

        return $responseService->success($summary, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
