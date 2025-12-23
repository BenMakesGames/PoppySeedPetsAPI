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
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/fishKebab")]
class FishKebabController
{
    #[Route("/{inventory}/takeApart", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fishKebab/#/takeApart');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You take the Fishkebab apart, receiving three pieces of Fish, and a Crooked Stick.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
