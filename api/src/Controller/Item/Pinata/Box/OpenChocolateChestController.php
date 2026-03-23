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
use App\Entity\Pet;
use App\Enum\PetLocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenChocolateChestController
{
    #[Route("/item/box/chocolate/{box}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openChocolateChest(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $box, 'box/chocolate/#/open');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $pets = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($pets) > 0)
        {
            /** @var Pet $pet */
            $pet = $rng->rngNextFromArray($pets);

            $description = 'The chest is locked. You struggle with it for a bit before ' . $pet->getName() . ' simply eats the lock. ';
            $pet
                ->increaseFood($rng->rngNextInt(2, 4))
                ->increaseEsteem(2)
            ;
        }
        else
        {
            $description = 'The chest is locked... so you eat the lock. ';
        }

        $possibleItems = [
            'Chocolate Bar', 'Chocolate Sword', 'Chocolate Cake Pops', 'Chocolate Meringue', 'Chocolate Syrup',
            'Chocolate Toffee Matzah', 'Chocolate-covered Honeycomb', 'Chocolate-frosted Donut',
            'Mini Chocolate Chip Cookies', 'Slice of Chocolate Cream Pie', 'Chocolate Key', 'Chocolate Teapot'
        ];

        $userStatsRepository->incrementStat($user, 'Looted ' . $box->getItem()->getNameWithArticle());

        $numberOfItems = $rng->rngNextInt(2, 6);

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $item = $inventoryService->receiveItem($rng->rngNextFromArray($possibleItems), $user, $box->getCreatedBy(), $user->getName() . ' found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

            $lootNames[] = $item->getItem()->getNameWithArticle();
        }

        $possibleGrossItems = [
            'Tomato "Sushi"', 'Tentacle', 'Hot Dog', 'Gefilte Fish', 'Egg Salad', 'Minestrone', 'Carrot Juice',
            'Onion Rings', 'Iron Sword',
        ];

        $grossItems = $numberOfItems == 2 ? 2 : 1;

        for($i = 0; $i < $grossItems; $i++)
        {
            $grossItem = $inventoryService->receiveItem($rng->rngNextFromArray($possibleGrossItems), $user, $box->getCreatedBy(), $user->getName() . ' found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
            $grossItem->setSpice(SpiceRepository::findOneByName($em, 'Chocolate-covered'));
            $lootNames[] = 'a Chocolate-covered ' . $grossItem->getItem()->getName();
        }

        $em->remove($box);

        sort($lootNames);

        $description .= 'Inside the chest, you find ' . ArrayFunctions::list_nice($lootNames) . '!';

        $em->flush();

        return $responseService->itemActionSuccess($description, [ 'itemDeleted' => true ]);
    }
}
