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
use App\Entity\Item;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/pizzaBox")]
class PizzaBoxController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openPizzaBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pizzaBox/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $newInventory = [];

        $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $possibleSlices = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->leftJoin('i.food', 'f')
            ->join('i.itemGroups', 'g')
            ->andWhere('f IS NOT NULL')
            ->andWhere('g.name = :za')
            ->setParameter('za', 'Za')
            ->getQuery()
            ->execute();

        $numSlices = $rng->rngNextFromArray([
            3, 3, 3, 3, 3, 4, 4, 4, 5, 6, // averages 3.8 (slightly less than 4, to avoid an infinite pizza box engine)
        ]);

        for($i = 0; $i < $numSlices; $i++)
        {
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray($possibleSlices), $user, $user, $description, $location, $locked)
                ->setSpice($inventory->getSpice())
            ;
        }

        return BoxHelpers::countRemoveFlushAndRespond('You open the box, finding', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

}