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
use App\Exceptions\PSPNotFoundException;
use App\Functions\InventoryHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class TripleChestController
{
    #[Route("/tripleChest/{inventory}/openWithIronKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithIronKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryHelpers::findOneToConsume($em, $user, 'Iron Key');

        if(!$key)
            throw new PSPNotFoundException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $rng->rngNextInt(10, $rng->rngNextInt(20, $rng->rngNextInt(50, $rng->rngNextInt(100, 200)))); // averages 35?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $possibleItems = [
            'Silver Bar', 'Silver Bar',
            'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Blackberry Wine',
            'Fluff',
            'Glowing Six-sided Die',
        ];

        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray($possibleItems), $user, $user, $comment, $location);

        $finalLoot = $rng->rngNextFromArray([
            'Major Scroll of Riches',
            'Scroll of Dice',
            'Noetala Egg',
        ]);

        $newInventory[] = $inventoryService->receiveItem($finalLoot, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/tripleChest/{inventory}/openWithSilverKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithSilverKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryHelpers::findOneToConsume($em, $user, 'Silver Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $rng->rngNextInt(15, $rng->rngNextInt(45, $rng->rngNextInt(100, $rng->rngNextInt(200, 300)))); // averages 50?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $items = [
            'Silver Bar',
            'Gold Bar',
            'Gold Bar',
            'Glowing Six-sided Die',
        ];

        $items[] = $rng->rngNextFromArray([
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Pepperbox',
        ]);

        $items[] = $rng->rngNextFromArray([
            'Scroll of Fruit',
            'Magic Hourglass',
            'Forgetting Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/tripleChest/{inventory}/openWithGoldKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithGoldKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryHelpers::findOneToConsume($em, $user, 'Gold Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Liquid-hot Magma',
        ];

        $items[] = $rng->rngNextFromArray([
            'Monster-summoning Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
