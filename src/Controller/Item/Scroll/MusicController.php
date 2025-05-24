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
class MusicController
{
    #[Route("/music/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeMusicScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/music/#/invoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::ReadAScroll);

        $commonItems = [
            'Flute', 'Fiberglass Flute', 'Music Note', 'Gold Triangle'
        ];

        $rareItems = [
            'Bass Guitar', 'Maraca', 'Melodica', 'Sousaphone'
        ];

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Music Note', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem($rng->rngNextFromArray($commonItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem($rng->rngNextFromArray($rareItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
        ];

        $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
        sort($itemList);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($itemList) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
