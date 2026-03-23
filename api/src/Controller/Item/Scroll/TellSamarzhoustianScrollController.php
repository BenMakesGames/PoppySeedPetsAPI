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
use App\Enum\UserStat;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/tellSamarzhoustianDelights")]
class TellSamarzhoustianScrollController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tellSamarzhoustianDelights/#/open');

        $ingredients = [
            'Algae',
            'Celery',
            'Corn',
            'Jellyfish Jelly',
            'Noodles',
            'Onion',
            'Seaweed',
        ];

        $spices = [
            'Nutmeg',
            'Onion Powder',
            'Spicy Spice',
            'Duck Sauce',
        ];

        $fancyItems = [
            'Everlasting Syllabub',
            'Bizet Cake',
            'Chili Calamari',
            'Mushroom Broccoli Krahi',
            'Poutine',
            'Qatayef',
            'Red Cobbler',
            'Shakshouka',
            'Tentacle Fried Rice',
            'Tentacle Onigiri',
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [
            $rng->rngNextFromArray($ingredients),
            $rng->rngNextFromArray($spices),
            $rng->rngNextFromArray($fancyItems),
        ];

        foreach($listOfItems as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        }

        $userStatsRepository->incrementStat($user, UserStat::ReadAScroll);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }
}
