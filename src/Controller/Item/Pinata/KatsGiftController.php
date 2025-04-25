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
use App\Functions\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/katsGift")]
class KatsGiftController
{
    #[Route("/{inventory}/chocolates", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getChocolates(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/chocolates');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $listOfItems = [
            $rng->rngNextFromArray([ 'Chocolate Sword', 'Chocolate Wine', 'Chocolate-covered Naner' ]),
            'Chocolate Bar', 'Chocolate Bar', 'Chocolate Bar',
            'Chocolate Syrup', 'Chocolate Syrup',
            'Chocolate Toffee Matzah', 'Chocolate Toffee Matzah', 'Chocolate Toffee Matzah',
            'Cocoa Powder', 'Cocoa Powder',
            'Chocolate Key'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        foreach($listOfItems as $itemName)
             $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/gardeningSupplies", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGardeningSupplies(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/chocolates');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $tool = $rng->rngNextFromArray([
            'Owl Trowel', 'Farmer\'s Multi-tool', 'Double Scythe',
        ]);

        $extraItems = [
            'Worker Bee', 'Worker Bee', 'Worker Bee',
            'Large Bag of Fertilizer', 'Large Bag of Fertilizer', 'Large Bag of Fertilizer', 'Large Bag of Fertilizer',
            $rng->rngNextFromArray([ 'Limestone', 'Rock' ]),
            $rng->rngNextFromArray([ 'Limestone', 'Rock' ]),
            'Tile: Run-down Orchard'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $toolItem = $inventoryService->receiveItem($tool, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked)
            ->setEnchantment(EnchantmentRepository::findOneByName($em, 'Fungilicious'));

        foreach($extraItems as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find a ' . $toolItem->getFullItemName() . ', ' . ArrayFunctions::list_nice($extraItems) . '!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/fishingGear", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFishingGear(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/fishingGear');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $tool = $rng->rngNextFromArray([
            'Ice Fishing', 'Sylvan Fishing Rod',
        ]);

        $enchantment = $rng->rngNextFromArray([
            'Captain\'s', 'Fish-frying', 'Meat-seeking',
        ]);

        $extraItems = [
            'Worms', 'Worms', 'Worms',
            'Sunscreen',
            $rng->rngNextFromArray([ 'Large, Yellow Plastic Bucket', 'Large Plastic Bucket' ]),
            'Everice',
            'Sportsball Oar', 'Sportsball Oar',
            $rng->rngNextFromArray([ 'Tile: Private Fishing Spot', 'Tile: Gone Fishing' ]),
            $rng->rngNextFromArray([ 'Cast Net', 'Fishing Recorder' ]),
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $toolItem = $inventoryService->receiveItem($tool, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked)
            ->setEnchantment(EnchantmentRepository::findOneByName($em, $enchantment));

        foreach($extraItems as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find a ' . $toolItem->getFullItemName() . ', ' . ArrayFunctions::list_nice($extraItems) . '!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/lava", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getLava(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/lava');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $itemList = [
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Liquid-hot Magma',
            'Rock'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        foreach($itemList as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find ten buckets of Liquid-hot Magma! ... and a Rock, apparently!', [ 'itemDeleted' => true ]);
    }
}
