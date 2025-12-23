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

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoxHelpers
{
    /**
     * @param Inventory[] $newInventory
     */
    public static function countRemoveFlushAndRespond(
        string $messagePrefix,
        UserStatsService $userStatsRepository, User $user, Inventory $inventory, array $newInventory,
        ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        $userStatsRepository->incrementStat($user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        $itemList = array_map(fn(Inventory $i) => InventoryModifierFunctions::getNameWithModifiers($i), $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($messagePrefix . ' ' . ArrayFunctions::list_nice($itemList) . '.', [ 'itemDeleted' => true ]);
    }
}