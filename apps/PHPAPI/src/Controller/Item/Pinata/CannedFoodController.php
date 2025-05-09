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
use App\Enum\UserStatEnum;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/item/cannedFood")]
class CannedFoodController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cannedFood/#/open');

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $cansOpened = $userStatsRepository->incrementStat($user, UserStatEnum::CANS_OF_FOOD_OPENED);

        if($cansOpened->getValue() > 3 && $rng->rngNextInt(1, 50) === 1)
        {
            $worms = $rng->rngNextInt(4, 12);

            for($i = 0; $i < $worms; $i++)
                $inventoryService->receiveItem('Worms', $user, $user, $user->getName() . ' found this in a can. A Canned Food can. Of worms.', $location, $lockedToOwner);

            $message = 'You open the can - AGK! IT WAS A CAN OF WORMS! (Despite this, you do also recycle the can, and get 1♺. Woo?)';
        }
        else
        {
            $item = $rng->rngNextFromArray([
                'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
                'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
                'Fermented Fish', 'Coffee Beans',
                'Tomato Soup', '"Chicken" Noodle Soup', 'Minestrone',
            ]);

            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a can. A Canned Food can.', $location, $lockedToOwner)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You open the can; it has ' . $item . ' inside! (You also recycle the can, and get 1♺. Woo.)';
        }

        $transactionService->getRecyclingPoints($user, 1, 'You recycled the can from some Canned Food.');

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
