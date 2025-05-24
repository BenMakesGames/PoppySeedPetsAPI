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
use App\Enum\PlayerActivityLogTagEnum;
use App\Enum\UserStatEnum;
use App\Functions\PlayerLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/eggplant")]
class EggplantController
{
    #[Route("/{inventory}/clean", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'eggplant/#/clean');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $r = $rng->rngNextInt(1, 6);
        $eggs = 0;

        if($r === 1)
        {
            $message = 'You clean the Eggplant as carefully as you can, but the insides are all horrible and rotten; in the end, nothing is recoverable! Stupid Eggplant! >:(';
        }
        else if($r === 2)
        {
            $eggs = 1;
            $message = 'You clean the Eggplant as carefully as you can, but most of it is no good, and you\'re only able to harvest one Egg! :(';
        }
        else if($r === 3 || $r === 4)
        {
            $eggs = 2;
            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. (Not too bad... right?)';
        }
        else if($r === 5)
        {
            $eggs = 2;

            $newItem = $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $newItem->setSpice($inventory->getSpice());

            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. You also manage to extract a good amount of purplish dye from the thing! (Neat!)';
        }
        else //if($r === 6)
        {
            $eggs = 3;
            $message = 'You clean the Eggplant as carefully as you can, and successfully harvest three Eggs!';
        }

        if($eggs > 0)
        {
            for($i = 0; $i < $eggs; $i++)
            {
                $newItem = $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

                $newItem->setSpice($inventory->getSpice());
            }

            $userStatsRepository->incrementStat($user, UserStatEnum::EggsHarvestedFromEggplants, $eggs);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::RottenEggplants, 1);
        }

        if($rng->rngNextInt(1, 100) === 1)
        {
            $inventoryService->receiveItem('Eggplant Bow', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            if($eggs === 0)
                $message .= ' Oh, but what\'s this? There\'s a purple bow inside! You clean it off, and keep it!';
            else
                $message .= ' Oh, and what\'s this? There\'s a purple bow inside! You clean it off, and keep it, as well!';
        }
        else if($rng->rngNextInt(1, 100) === 1)
        {
            $inventoryService->receiveItem('Mysterious Seed', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            if($eggs === 0)
                $message .= ' Oh, but what\'s this? There\'s a weird seed inside! You clean it off, and keep it!';
            else
                $message .= ' Oh, and what\'s this? There\'s a weird seed inside! You clean it off, and keep it, as well!';
        }

        $em->remove($inventory);

        PlayerLogFactory::create($em, $user, $message, [ PlayerActivityLogTagEnum::Item_Use ]);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
