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


namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Functions\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/scrollOfCrowns")]
class ScrollOfCrownsController
{
    #[Route("/{inventory}/vafs", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getVafsCrown(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfCrowns/#/vafs');

        $inventoryService->receiveItem('Vaf\'s Crown', $user, $user, $user->getName() . ' summoned this using a Scroll of Crowns.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The scroll melts into thin air, and you feel the weight of a crown in your hands - ah! It\'s Vaf\'s Crown!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/nirs", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getNirsCrown(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfCrowns/#/nirs');

        $inventoryService->receiveItem('Nir\'s Crown', $user, $user, $user->getName() . ' summoned this using a Scroll of Crowns.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The scroll melts into thin air, and you feel the weight of a crown in your hands - ah! It\'s Nir\'s Crown!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/gold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGoldCrown(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfCrowns/#/gold');

        $inventoryService->receiveItem('Gold Crown', $user, $user, $user->getName() . ' summoned this using a Scroll of Crowns.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The scroll melts into thin air, and you feel the weight of a crown in your hands - ah! It\'s a Gold Crown!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/lo-res", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getLoResCrown(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfCrowns/#/lo-res');

        $inventoryService->receiveItem('Lo-res Crown', $user, $user, $user->getName() . ' summoned this using a Scroll of Crowns.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The scroll melts into thin air, and you feel the _weightlessness_ of a crown in your hands? (Somehow?) Yes: it\'s a Lo-res Crown!', [ 'itemDeleted' => true ]);
    }

}
