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

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/metalDetector")]
class MetalDetectorController
{
    #[Route("/{inventory}/tune/iron", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForIron(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'metalDetector/#/tune/iron');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Iron)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/tune/silver", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForSilver(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'metalDetector/#/tune/silver');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Silver)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/tune/gold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tuneMetalDetectorForGold(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'metalDetector/#/tune/gold');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Metal Detector (Gold)'));

        $em->flush();

        $responseService->setReloadPets($inventory->getHolder() !== null);
        $responseService->setReloadInventory($inventory->getHolder() === null);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
