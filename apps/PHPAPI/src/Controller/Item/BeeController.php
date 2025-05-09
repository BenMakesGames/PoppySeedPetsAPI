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
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/bee")]
class BeeController
{
    #[Route("/{inventory}/giveToBeehive", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveToBeehive(
        Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'bee/#/giveToBeehive');

        if(!$user->getBeehive())
            return $responseService->itemActionSuccess("On second thought, it occurs to you that you don't know of any Beehive to put this Bee in...");

        $user->getBeehive()
            ->addWorkers(1)
            ->setFlowerPower(36)
            ->setInteractionPower()
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You introduce the Bee to Queen ' . $user->getBeehive()->getQueenName() . ', who thanks you for your honor and loyalty. The colony redoubles their efforts, and hey: with 1 more worker than before! (Every bee counts!)', [ 'itemDeleted' => true ]);
    }
}