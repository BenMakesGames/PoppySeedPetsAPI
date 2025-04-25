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
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/item/takeApart")]
class TakeApartController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function doIt(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'takeApart/#');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $takeApartTable = [
            'Glowing Russet Staff of Swiftness' => [
                'loot' => [ 'Hot Potato', 'Warm Potato', 'Potato' ],
                'verbing' => 'de-potato-ing'
            ],
            'Water Strider' => [
                'loot' => [ 'Hunting Spear', 'Cast Net' ],
                'verbing' => 'dismantling'
            ],
            'Lightning Axe' => [
                'loot' => [ 'Searing Blade', 'Searing Blade', 'Iron Bar' ],
                'verbing' => 'splitting',
            ]
        ];

        if(!array_key_exists($inventory->getItem()->getName(), $takeApartTable))
            throw new \Exception('Ben messed up and didn\'t make this item take-apartable :(');

        $info = $takeApartTable[$inventory->getItem()->getName()];

        foreach($info['loot'] as $item)
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' received this by ' . $info['verbing'] . ' ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());
        }

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(ucfirst($info['verbing']) . ' the ' . $inventory->getItem()->getName() . ' yielded ' . ArrayFunctions::list_nice($info['loot']) . '!', [ 'itemDeleted' => true ]);
    }
}
