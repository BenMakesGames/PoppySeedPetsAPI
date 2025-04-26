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
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/maraca")]
class MaracaController
{
    #[Route("/{inventory}/takeApart", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'maraca/#/takeApart');

        $location = $inventory->getLocation();

        $count = $rng->rngNextInt(2, 3);

        // definitely Beans
        $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of Magic Beans
        if($rng->rngNextInt(1, 2) === 1)
            $inventoryService->receiveItem('Magic Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);
        else
            $inventoryService->receiveItem('Everybeans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of extra Beans
        if($count === 3)
            $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take the Maraca apart, recuperating ' . $count . ' lots of Beans.' . "\n\nBecause that's totally how Beans are measured.\n\nIn \"lots\".", [ 'itemDeleted' => true ]);
    }
}
