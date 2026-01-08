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

class OpenHandicraftsBoxController
{
    #[Route("/item/box/handicrafts/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openHandicrafts(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/handicrafts/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $additionalItems = $rng->rngNextSubsetFromArray(
            [
                'Crooked Stick', 'String',
                'Fluff', 'Plastic', 'Green Dye', 'Yellow Dye', 'Paper', 'Glue',
                'Limestone', 'Glass', 'Iron Bar', 'Iron Ore', 'Silver Ore'
            ],
            8
        );

        $newInventory = [];

        foreach($additionalItems as $itemName)
        {
            if($itemName === 'Limestone')
                $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '. I don\'t know how it fit in there, either. Your guess is as good as mine.';
            else
                $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

            $newInventory[] = $inventoryService->receiveItem($itemName, $user, $user, $description, $location, $inventory->getLockedToOwner());
        }

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
