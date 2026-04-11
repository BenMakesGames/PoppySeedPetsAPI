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
use App\Enum\UserStat;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/mosquito")]
class MosquitoController
{
    #[Route("/{inventory}/swatMosquito", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function squishBug(
        Inventory $inventory, ResponseService $responseService, UserStatsService $userStatsRepository,
        EntityManagerInterface $em, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'mosquito/#/swatMosquito');

        $promised = UserQuestRepository::findOrCreate($em, $user, 'Promised to Not Squish Bugs', 0);

        if($promised->getValue())
            return $responseService->itemActionSuccess('You\'ve promised not to squish any more bugs... (even these guys!)');

        $location = $inventory->getLocation();
        $item = 'Blood "Jam"';
        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' swatted a mosquito, leaving this behind.', $location, false);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStat::BugsSquished);

        $em->flush();

        $responseService->addFlashMessage('You swatted the mosquito, which left behind a mess of ' . $item . '!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/extractBlood", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function putBugOutside(
        Inventory $inventory, ResponseService $responseService, UserStatsService $userStatsRepository,
        EntityManagerInterface $em, InventoryService $inventoryService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'mosquito/#/extractBlood');

        $location = $inventory->getLocation();
        $item = 'Blood "Jam"';
        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' extracted this from a mosquito.', $location, false);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStat::BugsPutOutside);
        $userStatsRepository->incrementStat($user, UserStat::ItemsRecycled);

        $em->flush();

        $responseService->addFlashMessage('You took ' . $item . ' from the mosquito, and put it outside.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

}
