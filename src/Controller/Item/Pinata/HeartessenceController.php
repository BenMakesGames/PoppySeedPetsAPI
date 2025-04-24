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
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/heartessence")]
class HeartessenceController extends AbstractController
{
    #[Route("/{inventory}/quintessence", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/quintessence');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 3; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and pulls itself apart into three motes of Quintessence!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/magicSmoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMagicSmoke(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/magicSmoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 3; $i++)
            $inventoryService->receiveItem('Magic Smoke', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and pulls itself apart into three wisps of Magic Smoke!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/hatBox", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getHatBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/magicSmoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Hat Box', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and folds itself into a Hat Box!', [ 'itemDeleted' => true ]);
    }
}
