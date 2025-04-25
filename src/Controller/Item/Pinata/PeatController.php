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
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/peat")]
class PeatController
{
    #[Route("/{inventory}/sort", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function raidPeat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'peat/#/sort');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $loot = array_merge(
            [ 'Large Bag of Fertilizer', 'Dark Matter', 'Fruit Fly' ],
            $rng->rngNextSubsetFromArray(
                [
                    'Small Bag of Fertilizer',
                    'Small Bag of Fertilizer',
                    'Fruit Fly',
                    'Worms',
                    'Crooked Stick',
                    'Charcoal',
                    'Rock',
                    'Really Big Leaf',
                    'Grandparoot',
                ],
                3
            )
        );

        sort($loot);

        foreach($loot as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in a clump of Barnacles.', $location, $lockedToOwner)
                ->setSpice($inventory->getSpice());
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('You sort through the peat, separating it into ' . ArrayFunctions::list_nice($loot) . '.', [ 'itemDeleted' => true ]);
    }
}
