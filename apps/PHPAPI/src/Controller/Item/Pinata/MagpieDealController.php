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

#[Route("/item/magpieDeal")]
class MagpieDealController
{
    #[Route("/{inventory}/quint", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/quint');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers two Quintessence. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/feathersAndEggs", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFeathersAndEggs(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/feathersAndEggs');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Feathers', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
            $inventoryService->receiveItem('Feathers', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
        ];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray([ 'Feathers', 'Egg' ]), $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers ' . ArrayFunctions::list_nice($itemList) . '. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/sticks", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSticks(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/sticks');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 5; $i++)
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers five Crooked Sticks. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/shinyMetals", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getShinyMetals(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/shinyMetals');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Silver Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Iron Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers an Iron, Silver, and Gold Bar. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }
}
