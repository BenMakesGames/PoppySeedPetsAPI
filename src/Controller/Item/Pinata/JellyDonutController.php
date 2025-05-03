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

#[Route("/item/jellyDonut")]
class JellyDonutController
{
    #[Route("/{inventory}/empty", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function empty(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'jellyDonut/#/empty');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $possibleJellies = [
            'Apricot Preserves',
            'Blackberry Jam',
            'Blueberry Jam',
            'Carrot Preserves',
            'Coffee Jelly',
            'Grass Jelly',
            'Jellyfish Jelly',
            'Naner Preserves',
            'Orange Marmalade',
            'Pamplemousse Marmalade',
            'Red Marmalade',
            'Royal Jelly',
            'Toad Jelly',
        ];

        $filling = $rng->rngNextFromArray($possibleJellies);

        $inventoryService->receiveItem($filling, $user, $user, $user->getName() . ' got this from a Jelly-filled Donut.', $location, $lockedToOwner);

        $message = 'You scoop the jelly out of the donut... Ah! ' . $filling . '!';

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Chocolate-frosted Donut'));

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}