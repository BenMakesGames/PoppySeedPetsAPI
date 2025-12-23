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

#[Route("/item/apricrate")]
class ApricrateController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserAccessor $userAccessor, IRandom $rng, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'apricrate/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        // other seasonal fruit:
        $extraItem = ItemRepository::findOneByName($em, $rng->rngNextFromArray([
            'Blueberries', 'Blueberries', 'Blueberries',
            'Eggplant',
            'Mango', 'Mango',
            'Melowatern',
            'Spicy Peps'
        ]));

        $loot = [
            'Apricot', 'Apricot', 'Apricot', 'Apricot', 'Apricot', 'Apricot', 'Apricot',
            $extraItem
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Empty Crate'))
            ->addComment('This was once in Apricrate, but ' . $user->getName() . ' emptied it of its Apricots.')
        ;

        $userStatsService->incrementStat($user, 'Apricrates Raided');

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('You loot the crate, finding seven Apricots, and - hm! - how\'d ' . $extraItem->getNameWithArticle() . ' get in there? Quelle suprise!', [ 'itemDeleted' => true ]);
    }
}
