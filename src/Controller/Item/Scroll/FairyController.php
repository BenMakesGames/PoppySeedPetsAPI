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


namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/scroll")]
class FairyController extends AbstractController
{
    #[Route("/fairy/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readFairyScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/fairy/#/read');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $lameItems = [ 'Toadstool', 'Charcoal', 'Toad Legs', 'Bird\'s-foot Trefoil', 'Coriander Flower' ];

        $loot = [
            'Wings',
            $rng->rngNextFromArray($lameItems),
            $rng->rngNextFromArray($lameItems),
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' summoned this by reading a Fairy\'s Scroll.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($loot) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
