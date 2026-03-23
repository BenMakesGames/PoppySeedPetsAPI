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
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenTwilightBoxController
{
    #[Route("/item/box/twilight/{box}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openTwilightBox(
        Inventory $box, ResponseService $responseService,
        EntityManagerInterface $em, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $box, 'box/twilight/#/open');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $items = [ 'Grandparoot', 'Cobweb' ];

        $possibleItems = [
            'Quinacridone Magenta Dye', 'Moon Pearl', 'Jar of Fireflies', 'Quintessence',
            'Candle', 'Dreamwalker\'s Tea', 'Eggplant', 'Glowing Protojelly'
        ];

        shuffle($possibleItems);

        for($i = 0; $i < 4; $i++)
            $items[] = $possibleItems[$i];

        shuffle($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $box->getCreatedBy(), 'Found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($box);
        $em->flush();

        $message = 'Rummaging through the box, you find ' . ArrayFunctions::list_nice($items, ', ', ', aaaaaand... ') . '!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
