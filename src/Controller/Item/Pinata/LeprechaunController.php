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

#[Route("/item/leprechaun")]
class LeprechaunController extends AbstractController
{
    #[Route("/potOfGold/{inventory}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function lootPotOfGold(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leprechaun/potOfGold/#/loot');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::LOOTED_A_POT_OF_GOLD);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Rainbow', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Empty Cauldron', $user, $user, 'The remains of a Pot of Gold that ' . $user->getName() . ' looted.', $location, $locked);

        $em->flush();

        $responseService->addFlashMessage('You find three Gold Bars! Oh: and the Rainbow! Oh: and keep the Empty Cauldron, too. Why not.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/greenScroll/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readGreenScroll(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        ResponseService $responseService, UserStatsService $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leprechaun/greenScroll/#/read');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);
        $userStatsRepository->incrementStat($user, 'Read ' . $inventory->getItem()->getNameWithArticle());

        $numberOfItems = 3;

        $possibleItems = [
            'Green Egg', 'Green Gummies', 'Short Glass of Greenade', 'Green Bow', 'Green Muffin'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [];

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $item = $rng->rngNextFromArray($possibleItems);
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
            $listOfItems[] = $item;
        }

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
