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
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/box")]
class HatBoxController
{
    #[Route("/hat/{box}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openHatBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $box, 'box/hat/#/open');

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $hatItem = ItemRepository::findOneByName($em, $rng->rngNextFromArray([
            'Bright Top Hat',
            'Cool Sunglasses',
            'Crabhat',
            'Dark Horsey Hat',
            'Eccentric Top Hat',
            'Gray Bow',
            'Horsey Hat',
            'Judy',
            'Masquerade Mask',
            'Merchant\'s Cap',
            'Merchant\'s Other Cap',
            'Pizzaface',
            'Propeller Beanie',
            'Sombrero',
            'Wizarding Hat',
        ]));

        $userStatsRepository->incrementStat($user, 'Opened ' . $box->getItem()->getNameWithArticle());

        if($hatItem->getName() === 'Gray Bow')
        {
            $itemComment = 'Made out of the strap of ' . $box->getItem()->getNameWithArticle() . '.';
            $message = "You open the hat box... ta-da! It\'s... EMPTY?!?!\n\nRefusing to be outdone by a box, you tie the Hat Box\'s strap into a bow.";
        }
        else if($hatItem->getName() === 'Cool Sunglasses')
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s... ' . $hatItem->getNameWithArticle() . '? (Is that a hat?)';
        }
        else
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s ' . $hatItem->getNameWithArticle() . '!';
        }

        $inventoryService->receiveItem($hatItem, $user, $box->getCreatedBy(), $itemComment, $location, $lockedToOwner);

        $em->remove($box);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
