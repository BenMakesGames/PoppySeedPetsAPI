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

namespace App\Controller\Item\Pinata\Box;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenSmallOreBoxController
{
    #[Route("/item/box/smallOres/{box}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openSmallOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $box, 'box/smallOres/#/loot');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $possibleOres = [
            'Iron Ore', 'Iron Ore', 'Iron Ore',
            'Silver Ore', 'Silver Ore', 'Silver Ore',
            'Gold Ore', 'Gold Ore',
            'XOR',
        ];

        $numberOfItems = $rng->rngNextInt(1, 3) == 1 ? 2 : 3;

        for($i = 0; $i < $numberOfItems; $i++)
            $inventoryService->receiveItem($rng->rngNextFromArray($possibleOres), $user, $box->getCreatedBy(), 'Found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($box);

        $message = 'Sifting through the box, you found ' . $numberOfItems . ' good chunks of ore!';

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
