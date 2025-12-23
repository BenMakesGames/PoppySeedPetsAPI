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
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/scarfBag")]
class ScarfBagController
{
    #[Route("/{bag}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openScarfBag(
        Inventory $bag, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $bag, 'scarfBag/#/open');
        ItemControllerHelpers::validateLocationSpace($bag, $em);

        $location = $bag->getLocation();
        $lockedToOwner = $bag->getLockedToOwner();



        $scarf = $rng->rngNextFromArray([
            'North Star Scarf',
            'Pine Green Scarf',
            'Rainbow Scarf',
            'Betelgeuse Scarf',
            'Freddy Scarf',
            'Cheshire Scarf',
            'Toothpaste Scarf',
            'Starry Night Scarf',
            'Black Scarf',
            'Memories of Summer',
        ]);

        $inventoryService->receiveItem($scarf, $user, $bag->getCreatedBy(), 'Found inside a Scarf Bag.', $location, $lockedToOwner);

        $em->remove($bag);

        $em->flush();

        $message = 'You open the bag, and find a ' . $scarf . ' inside!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
