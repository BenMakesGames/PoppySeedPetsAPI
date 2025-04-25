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
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/fairyRing")]
class FairyRingController
{
    #[Route("/{inventory}/takeApart", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeApart(
        Inventory $inventory, ResponseService $responseService, IRandom $rng,
        EntityManagerInterface $em, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fairyRing/#/takeApart');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Gold Ring'));

        $inventoryService->receiveItem('Wings', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled these off a Fairy Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $message = 'You pull the Wings off the Fairy Ring. Now it\'s just a regular Gold Ring.';

        if($rng->rngNextInt(1, 70) === 1)
            $message .= $rng->rngNextFromArray([ ' (I hope you\'re happy.)', ' (See what thy hand hath wrought!)', ' (All according to plan...)' ]);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);

    }
}
