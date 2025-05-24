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


namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/scroll")]
class SeaController
{
    #[Route("/sea/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeSeaScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/sea/#/invoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::ReadAScroll);

        $items = [
            'Fish',
            'Seaweed',
            'Silica Grounds',
            $rng->rngNextFromArray([ 'Fish', 'Tentacle' ]),
            $rng->rngNextFromArray([ 'Seaweed', 'Fish' ]),
            $rng->rngNextFromArray([ 'Seaweed', 'Silica Grounds' ]),
            $rng->rngNextFromArray([ 'Seaweed', 'Crooked Stick' ]),
        ];

        if($rng->rngNextInt(1, 4) === 1) $items[] = 'Glass';
        if($rng->rngNextInt(1, 5) === 1) $items[] = 'Music Note';
        if($rng->rngNextInt(1, 8) === 1) $items[] = 'Mermaid Egg';
        if($rng->rngNextInt(1, 10) === 1) $items[] = 'Secret Seashell';
        if($rng->rngNextInt(1, 15) === 1) $items[] = 'Iron Ore';
        if($rng->rngNextInt(1, 20) === 1) $items[] = 'Little Strongbox';
        if($rng->rngNextInt(1, 45) === 1) $items[] = 'Ceremony of Sand and Sea';

        $location = $inventory->getLocation();

        sort($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, summoning ' . ArrayFunctions::list_nice($items) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
