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

class OpenMonsterBoxController
{
    #[Route("/item/box/monster/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openMonsterBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/monster/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        /** @var Inventory[] $newInventory */
        $newInventory = [];

        $location = $inventory->getLocation();
        $spice = $inventory->getSpice();

        $quantities = [2, 3, 4];
        shuffle($quantities);

        for($i = 0; $i < $quantities[0]; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Egg', 'Fluff', 'Feathers', 'Talon', 'Duck Sauce', 'Worms' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[1]; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Fish', 'Scales', 'Toad Legs', 'Tentacle', 'Sand Dollar', 'Jellyfish Jelly' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[2]; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Creamy Milk', 'Butter', 'Plain Yogurt', 'Oil', 'Mayo(nnaise)' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($spice)
        {
            shuffle($newInventory);

            for($i = 0; $i < count($newInventory) / 3; $i++)
                $newInventory[$i]->setSpice($spice);
        }

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
