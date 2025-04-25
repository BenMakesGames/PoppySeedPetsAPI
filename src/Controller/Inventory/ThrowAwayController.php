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

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\RecyclingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/inventory")]
class ThrowAwayController
{
    #[Route("/throwAway", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function throwAway(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em, RecyclingService $recyclingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) > 200)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to recycle more than 200 items at a time. Sorry.');

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some of the items could not be found??');

        $idsNotRecycled = $recyclingService->recycleInventory($user, $inventory);

        $em->flush();

        return $responseService->success($idsNotRecycled);
    }
}
