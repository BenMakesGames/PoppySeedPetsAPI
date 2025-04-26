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
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/box")]
class StrongboxController
{
    #[Route("/little-strongbox/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openLittleStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/little-strongbox/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryRepository::findOneToConsume($em, $user, 'Iron Key');

        if(!$key)
            throw new PSPNotFoundException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $rng->rngNextInt(10, 40);

        if($rng->rngNextInt(1, 10) === 1)
            $moneys *= 2;

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $possibleItems = [
            'Seaweed',
            'Silver Colander',
            'Gold Tuning Fork',
            '"Rustic" Magnifying Glass',
            'Butterknife',
            'Blackberry Wine',
            'Fluff',
            'Glowing Six-sided Die',
        ];
        shuffle($possibleItems);

        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($possibleItems[$i], $user, $user, $comment, $location);

        $newInventory[] = $inventoryService->receiveItem('Piece of Cetgueli\'s Map', $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/very-strongbox/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/very-strongbox/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryRepository::findOneToConsume($em, $user, 'Silver Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $rng->rngNextInt(20, 60);

        if($rng->rngNextInt(1, 10) === 1)
            $moneys *= 2;

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $items = [
            $rng->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]),
            $rng->rngNextFromArray([ 'Rusty Blunderbuss', 'Rusty Rapier', 'Pepperbox' ]),
            $rng->rngNextFromArray([ 'Minor Scroll of Riches', 'Hourglass' ]),
            $rng->rngNextFromArray([ 'Scroll of Fruit', 'Scroll of the Sea', 'Gold Ring' ])
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/outrageously-strongbox/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openOutrageouslyStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/outrageously-strongbox/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = InventoryRepository::findOneToConsume($em, $user, 'Gold Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $rng->rngNextInt(30, 80);

        if($rng->rngNextInt(1, 10) === 1)
            $moneys *= 2;

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $items = [
            $rng->rngNextFromArray([ 'Major Scroll of Riches', 'Magic Hourglass' ]),
            $rng->rngNextFromArray([ 'Rusty Blunderbuss', 'Rusty Rapier', 'Password' ]),
            $rng->rngNextFromArray([ 'Spice Rack', 'Gold Telescope', 'Tile: Silver Vein' ]),
            $rng->rngNextFromArray([ 'Hat Box', 'Forgetting Scroll', 'Glowing Protojelly' ])
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
