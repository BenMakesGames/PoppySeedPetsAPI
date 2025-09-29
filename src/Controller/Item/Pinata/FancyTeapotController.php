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
use App\Enum\UserStat;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/fancyTeapot")]
class FancyTeapotController
{
    #[Route("/{inventory}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBurntLog(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fancyTeapot/#/smash');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $teaBases = [
            'Black Tea',
            'Coffee Bean Tea',
            'Coffee Bean Tea with Mammal Extract',
            'Dreamwalker\'s Tea',
            'Ginger Tea',
            'Sweet Black Tea',
            'Sweet Coffee Bean Tea',
            'Sweet Coffee Bean Tea with Mammal Extract',
            'Sweet Ginger Tea',
            'Sweet Tea with Mammal Extract',
            'Tea with Mammal Extract',
            'Tiny Tea',
            'Totally Tea',
            'Tremendous Tea'
        ];

        $teaSpices = [
            '5-Spice\'d',
            'Cosmic',
            'Nutmeg-laden',
            'Rain-scented',
            'Spicy',
            'Sweet & Spicy',
            'Tropical',
            'with Dandelion Syrup',
            'with Flavors Unknown',
            'with Mélange',
        ];

        $teas = $rng->rngNextSubsetFromArray($teaBases, 5);
        $spices = $rng->rngNextSubsetFromArray($teaSpices, 5);

        for($i = 0; $i < 5; $i++)
        {
            $receivedItem = $inventoryService->receiveItem($teas[$i], $user, $user, $user->getName() . ' found this inside a Fancy Teapot. By smashing it? Apparently.', $location, $lockedToOwner);

            $receivedItem->setSpice(SpiceRepository::findOneByName($em, $spices[$i]));

            $receivedTeaNames[] = $receivedItem->getFullItemName();
        }

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, 'Smashed a Fancy Teapot');

        $em->flush();

        return $responseService->itemActionSuccess('You smash the teapot to bits, recovering the precious teas within: ' . ArrayFunctions::list_nice($receivedTeaNames) . '.', [ 'itemDeleted' => true ]);
    }
}
