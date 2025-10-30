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
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/exclusiveOre")]
class ExclusiveOreController
{
    #[Route("/{inventory}/ironOre", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getIronOre(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'exclusiveOre/#/ironOre');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Iron Ore'));

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('This one is exclusively an Iron Ore.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/silverOre", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSilverOre(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'exclusiveOre/#/silverOre');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Silver Ore'));

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('This one is exclusively a Silver Ore.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/goldOre", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGoldOre(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'exclusiveOre/#/goldOre');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Gold Ore'));

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('This one is exclusively a Gold Ore.', [ 'itemDeleted' => true ]);
    }
}
