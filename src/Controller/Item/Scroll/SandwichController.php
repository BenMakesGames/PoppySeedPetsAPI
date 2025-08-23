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
use App\Entity\Item;
use App\Enum\UserStat;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use PHPStan\Internal\ArrayHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/scroll/sandwich")]
class SandwichController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readSandwichScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/sandwich/#/read');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        // it's okay if the player summons another Sandwill Scroch - that's more fun :P
        $sandwiches = $em->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->join('i.itemGroups', 'ig')
            ->where('ig.name = :sandwich')
            ->setParameter('sandwich', 'Sandwich')
            ->getQuery()
            ->getResult();

        $readDescription = $rng->rngNextBool()
            ? 'You read the, um, scroch(?), and'
            : 'You read the, um, _scroch_, and';

        if($rng->rngNextInt(1, 20) === 1)
        {
            $number = $rng->rngNextInt(5, 6);
            $numberWord = $number == 5 ? 'five' : 'six';
            $lastSeparator = ', and... ';
            $readDescription .= '-- whoa! It must have been _perfectly_ cooked: **' . $numberWord . '** sammies come flying out!';
        }
        else
        {
            $number = 3;
            $lastSeparator = ', and ';
            $readDescription .= ' three sammies come flying out!';
        }

        $sandwichesSummoned = $rng->rngNextSubsetFromArray($sandwiches, $number);
        $sandwichNames = [];

        foreach($sandwichesSummoned as $sandwichSummoned)
        {
            $sandwichNames[] = $sandwichSummoned->getNameWithArticle();
            $inventoryService->receiveItem($sandwichSummoned, $user, $user, $user->getName() . ' summoned this by reading a Sandwill Scroch.', $location, $lockedToOwner);
        }

        $userStatsRepository->incrementStat($user, UserStat::ReadAScroll);

        $em->remove($inventory);

        $readDescription .= ' ' . mb_ucfirst(ArrayFunctions::list_nice($sandwichNames, lastSeparator: $lastSeparator)) . '!';

        return $responseService->itemActionSuccess($readDescription, [ 'itemDeleted' => true ]);
    }
}