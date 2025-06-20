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
use App\Exceptions\PSPInvalidOperationException;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/heartstone")]
class HeartstoneController
{
    private const string StatName = 'Transformed a Heartstone';

    #[Route("/{inventory}/transform", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function transform(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartstone/#/transform');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $numberTransformed = $userStatsRepository->getStatValue($user, self::StatName);
        $petsWhoHaveCompletedHeartDimensionAdventures = $userStatsRepository->getStatValue($user, 'Pet Completed the Heartstone Dimension');

        $numberThatCanBeTransformed = $petsWhoHaveCompletedHeartDimensionAdventures - $numberTransformed;

        if($numberThatCanBeTransformed <= 0)
        {
            if($numberTransformed == 0)
                throw new PSPInvalidOperationException('You cannot transform a Heartstone until one of your pets has completed all of the Heartstone Dimension challenges.');
            else
                throw new PSPInvalidOperationException('You cannot transform any more Heartstones until another one of your pets has completed all of the Heartstone Dimension challenges.');
        }

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Heartessence', $user, $user, $user->getName() . ' got this by transforming a Heartstone.', $location, $locked);

        $userStatsRepository->incrementStat($user, self::StatName);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartstone evaporates, releasing two Heartessences!', [ 'itemDeleted' => true ]);
    }
}
