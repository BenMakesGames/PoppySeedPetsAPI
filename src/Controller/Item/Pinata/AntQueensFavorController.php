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
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/antQueensFavor")]
class AntQueensFavorController
{
    #[Route("/{inventory}/bugStuff", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getBugStuff(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'antQueensFavor/#/bugStuff');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $possibleItems = [
            'Ants on a Log',
            'Trowel',
            'Magic Smoke',
            'NUL',
            'Rock',
            'Glowing Six-sided Die',
            'Mysterious Seed',
            'Paper Bag',
            'Really Big Leaf',
            'Spider Roe',
            'Tile: Bakery Bites',
            'Tile: Preying Mantis',
            'Wolf\'s Bane',
            'Petrichor',
            'Antenna',
            'Large Bag of Fertilizer',
            'Eggplant',
        ];

        $items = $rng->rngNextSubsetFromArray($possibleItems, 10);

        sort($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Ant Queen\'s Favor.', $location);

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('A line of ants approaches with ' . ArrayFunctions::list_nice($items) . '. "Thus concludes our deal!" they chitter, before scurrying away.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/candy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFood(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'antQueensFavor/#/candy');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $possibleItems = [
            'Caramel',
            'Chocolate Toffee Matzah',
            'Gummy Worms',
            'Green Gummies',
            'Konpeitō',
            'Ladyfingers',
            'Meringue',
            'Mixed Nut Brittle',
            'Mochi',
            'Orange Gummies',
            'Orange Hard Candy',
            'Orange Chocolate Bar',
            'Purple Gummies',
            'Qabrêk Splàdj',
            'Red Hard Candy',
            'Sugar',
            'Treat of Crispy Rice',
            'Yellow Gummies',
            'Yellow Hard Candy',
        ];

        $items = $rng->rngNextSubsetFromArray($possibleItems, 14);

        sort($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Ant Queen\'s Favor.', $location);

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('A line of ants approaches with ' . ArrayFunctions::list_nice($items) . '. "Thus concludes our deal!" they chitter, before scurrying away.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/craftingSupplies", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function craftingSupplies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'antQueensFavor/#/craftingSupplies');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $possibleItems = [
            'Fiberglass',
            'Glue',
            'Iron Bar',
            'Silver Bar',
            'Hand Rake',
            'Magic Smoke',
            'Paint Stripper',
            'Potion of Crafts',
            'Rusted, Busted Mechanism',
            'Scroll of Resources',
            'String',
            'White Cloth',
            'Tiny Scroll of Resources',
            'Megalium',
            'Metal Detector (Iron)',
            'Tri-color Scissors',
            'Quintessence',
        ];

        $items = $rng->rngNextSubsetFromArray($possibleItems, 12);

        sort($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Ant Queen\'s Favor.', $location);

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('A line of ants approaches with ' . ArrayFunctions::list_nice($items) . '. "Thus concludes our deal!" they chitter, before scurrying away.', [ 'itemDeleted' => true ]);
    }
}
