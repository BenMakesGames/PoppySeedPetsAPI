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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/hotPotato")]
class HotPotatoController
{
    #[Route("/{inventory}/toss", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, IRandom $rng, HotPotatoService $hotPotatoService,
        UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $numberOfTosses = HotPotatoService::countTosses($inventory);

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/toss');

        if($rng->rngNextInt(1, 10 + $numberOfTosses) <= $numberOfTosses + 1)
        {
            $spice = $inventory->getSpice();

            $allItemNames = [
                'Smashed Potatoes',
                'Liquid-hot Magma',
            ];

            for($i = 0; $i < $numberOfTosses; $i++)
            {
                $allItemNames[] = $rng->rngNextFromArray([
                    'Smashed Potatoes',
                    'Liquid-hot Magma',
                    'Butter',
                    'Oil',
                    'Sour Cream',
                    'Cheese',
                    'Vinegar',
                    'Onion',
                    'Beans',
                ]);
            }

            sort($allItemNames);

            foreach($allItemNames as $itemName)
            {
                $inventoryService->receiveItem($itemName, $user, $inventory->getCreatedBy(), 'This exploded out of a Hot Potato.', $inventory->getLocation())
                    ->setSpice($spice);
                ;
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Hot Potato, but it explodes in your hands! It\'s a bit hot, but hey: you got ' . ArrayFunctions::list_nice($allItemNames) . '!', [ 'itemDeleted' => true ]);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::TOSSED_A_HOT_POTATO);

            return $hotPotatoService->tossItem($inventory);
        }
    }

    #[Route("/{inventory}/tossChocolateBomb", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tossChocolateBomb(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, IRandom $rng, HotPotatoService $hotPotatoService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/tossChocolateBomb');

        $numberOfTosses = HotPotatoService::countTosses($inventory);

        if($rng->rngNextInt(1, 100) <= 10 + $numberOfTosses * 10)
        {
            $numberOfItems = 5 + $numberOfTosses;
            $spice = $inventory->getSpice();

            $loot = $rng->rngNextSubsetFromArray([
                'Chocolate Bar',
                'Chocolate Bomb',
                'Chocolate Cake Pops',
                'Chocolate Chip Meringue',
                'Chocolate Chip Muffin',
                'Chocolate Ice Cream',
                'Chocolate Key',
                'Chocolate Meringue',
                'Chocolate Syrup',
                'Chocolate Toffee Matzah',
                'Chocolate-covered Honeycomb',
                'Chocolate-covered Naner',
                'Chocolate-frosted Donut',
                'Mini Chocolate Chip Cookies',
                'Orange Chocolate Bar',
                'Slice of Chocolate Cream Pie',
                'Spicy Chocolate Bar'
            ], $numberOfItems);

            foreach($loot as $itemName)
            {
                $inventoryService->receiveItem($itemName, $user, $inventory->getCreatedBy(), 'This exploded out of a Chocolate Bomb.', $inventory->getLocation(), $itemName === 'Chocolate Bomb')
                    ->setSpice($spice);
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Chocolate Bomb, but it explodes in your hands; ' . $numberOfItems . ' chocolately items fly out!', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }

    #[Route("/{inventory}/tossHongbao", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function tossHongbao(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        IRandom $rng, HotPotatoService $hotPotatoService, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/tossHongbao');

        if($rng->rngNextInt(1, 5) === 1)
        {
            $money = $rng->rngNextInt(10, 20);

            $transactionService->getMoney($user, $money, "Found this inside {$inventory->getItem()->getNameWithArticle()}.");

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess("You try to open the {$inventory->getItem()->getName()}, and succeed! (Just like a real envelope _should_ work!) There's {$money}~~m~~ inside, which you pocket.", [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory, "You try to open the {$inventory->getItem()->getName()}, but, mysteriously, it refuses. Eventually you give up, and toss it");
        }
    }
}
