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
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item")]
class LeafSpearController
{
    #[Route("/leafSpear/{inventory}/unwrap", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function unwrapLeafSpear(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leafSpear/#/unwrap');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Really Big Leaf'))
            ->addComment($user->getName() . ' untied a Leaf Spear, causing it to unroll into this.')
        ;

        $inventoryService->receiveItem('String', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled this off of a Leaf Spear.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        return $responseService->itemActionSuccess('You untie the String, and the leaf practically unrolls on its own.', [ 'itemDeleted' => true ]);
    }
}
