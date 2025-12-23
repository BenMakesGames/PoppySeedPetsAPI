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

#[Route("/item/spiceRack")]
class SpiceRackController
{
    #[Route("/{inventory}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function loot(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'spiceRack/#/loot');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $spices = $rng->rngNextSubsetFromArray([
            'Cheesy Flakes',
            'Fool\'s Spice',
            'Freshly-squeezed Fish Oil',
            'Nutmeg',
            'Onion Powder',
            'Spicy Spice',
            'Spider Roe',
            'Artificial Grape Extract',
        ], 3);

        foreach($spices as $spice)
            $inventoryService->receiveItem($spice, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You raid the spice rack, finding ' . ArrayFunctions::list_nice($spices) . '!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
