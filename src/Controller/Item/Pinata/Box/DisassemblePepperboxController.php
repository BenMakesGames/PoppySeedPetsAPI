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

namespace App\Controller\Item\Pinata\Box;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DisassemblePepperboxController
{
    #[Route("/item/box/pepperbox/{inventory}/disassemble", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function disassemblePepperbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/pepperbox/#/disassemble');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $peppers = $rng->rngNextInt(4, $rng->rngNextInt(6, 10));

        $description = $user->getName() . ' got this by taking apart ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $peppers; $i++)
        {
            $inventoryService->receiveItem('Spicy Peps', $user, $user, $description, $location, $inventory->getLockedToOwner())
                ->setSpice($inventory->getSpice())
            ;
        }

        $userStatsRepository->incrementStat($user, 'Disassembled ' . $inventory->getItem()->getNameWithArticle());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take apart the Pepperbox into its constituent pieces...', [ 'itemDeleted' => true ]);
    }
}
