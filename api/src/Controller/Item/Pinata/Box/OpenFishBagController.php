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

namespace App\Controller\Item\Pinata\Box;

use App\Controller\Item\ItemControllerHelpers;
use App\Controller\Item\Pinata\BoxHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenFishBagController
{
    #[Route("/item/box/fishBag/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openFishBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/fishBag/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $newInventory = [];

        $location = $inventory->getLocation();

        $message = $user->getName() . ' got this from a Fish Bag.';

        $newInventory[] = $inventoryService->receiveItem('Fish', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Fish', $user, $user, $message, $location, $inventory->getLockedToOwner());

        $otherDrops = [
            'Fish',
            'Fish',
            'Silica Grounds',
            'Silica Grounds',
            'Crooked Stick',
            'Sand Dollar',
            'Tentacle',
            'Mermaid Egg',
        ];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray($otherDrops), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($rng->rngNextInt(1, 5) === 1)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Jellyfish Jelly', 'Small, Yellow Plastic Bucket' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($rng->rngNextInt(1, 20) === 1)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Merchant Fish', 'Secret Seashell', 'Ceremonial Trident' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Opening the bag revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
