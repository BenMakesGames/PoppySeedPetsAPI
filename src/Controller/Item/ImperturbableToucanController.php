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

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/toucan")]
class ImperturbableToucanController
{
    #[Route("/{inventory}/setFree", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setFree(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        InventoryService $inventoryService, UserAccessor $userAccessor, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'toucan/#/setFree');

        $inventoryService->receiveItem('Cereal Box', $user, $user, $user->getName() . ' found this in their window sill. Just totally incidentally.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $userStatsService->incrementStat($user, UserStatEnum::SET_A_TOUCAN_FREE, 1);

        $em->flush();

        return $responseService->itemActionSuccess("The toucan moves with a speed - or, rather, a slowness - demonstrative of an almost complete indifference towards your words. Still, it eventually works its way to a window, and glides off into the jungle.\n\nOh, there's a Cereal Box on the window sill? Huh. You must have left that there forever ago and forgotten all about it.", [ 'itemDeleted' => true ]);
    }
}