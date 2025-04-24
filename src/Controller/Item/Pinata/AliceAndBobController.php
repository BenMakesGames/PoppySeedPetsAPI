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
use App\Functions\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/box")]
class AliceAndBobController extends AbstractController
{
    #[Route("/alicesSecret/{inventory}/teaTime", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretTeaTime(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/teaTime');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Toadstool', 'Shortbread Cookies'
        ];

        for($i = 0; $i < 3; $i++)
        {
            $loot[] = $rng->rngNextFromArray([
                'Coffee Bean Tea with Mammal Extract',
                'Ginger Tea',
                'Black Tea',
                'Sweet Tea with Mammal Extract',
            ]);
        }

        for($i = 0; $i < 2; $i++)
        {
            if($rng->rngNextInt(1, 5) === 1)
            {
                $loot[] = $rng->rngNextFromArray([
                    'Dreamwalker\'s Tea', 'Yogurt Muffin',
                ]);
            }
            else
            {
                $loot[] = $rng->rngNextFromArray([
                    'Toadstool', 'Mini Chocolate Chip Cookies', 'Pumpkin Bread',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/alicesSecret/{inventory}/hourglass", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretHourglass(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/hourglass');

        $item = $inventoryService->receiveItem('Hourglass', $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em);
    }

    #[Route("/alicesSecret/{inventory}/cards", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretCards(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/cards');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Paper', 'Paper', 'Paper', 'Paper', $rng->rngNextFromArray([ 'Paper', 'Quinacridone Magenta Dye' ])
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find some cards? Oh, wait, no: it\'s just', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/fish", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsSecretFish(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/fish');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Fish',
            'Fish',
            'Scales'
        ];

        for($i = 0; $i < 3; $i++)
        {
            if($rng->rngNextInt(1, 5) === 1)
            {
                $loot[] = $rng->rngNextFromArray([
                    'Sand Dollar', 'Tentacle',
                ]);
            }
            else
            {
                $loot[] = $rng->rngNextFromArray([
                    'Fish', 'Scales',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/tool", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsTool(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/tool');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        // apply "Bob's" bonus
        $tool = $rng->rngNextFromArray([
            'Iron Tongs',
            'Garden Shovel',
            'Crooked Fishing Rod',
            'Yellow Scissors',
            'Small Plastic Bucket',
            'Straw Broom',
        ]);

        $item = $inventoryService->receiveItem($tool, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $item->setEnchantment(
            EnchantmentRepository::findOneByName($em, 'Bob\'s')
        );

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/bbq", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsBBQ(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/bbq');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Charcoal',
            'Hot Dog',
            'Grilled Fish',
            'Tomato Ketchup',
            'Hot Potato'
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
