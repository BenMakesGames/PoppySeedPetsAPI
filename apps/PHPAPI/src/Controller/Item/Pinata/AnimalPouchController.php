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
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/animalPouch")]
class AnimalPouchController
{
    #[Route("/magpie/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openMagpiePouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'animalPouch/magpie/#/open');

        $possibleItems = [
            'Fool\'s Spice',
            $rng->rngNextFromArray([ '"Gold" Idol', 'Phishing Rod' ]),
            $rng->rngNextFromArray([ 'Glass', 'Crystal Ball' ]),
            'Mixed Nuts',
            $rng->rngNextFromArray([ 'Fluff', 'String' ]),
            'Sand Dollar'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = $rng->rngNextSubsetFromArray($possibleItems, 3);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

            if($itemName === 'Phishing Rod')
                $item->setEnchantment(EnchantmentRepository::findOneByName($em, 'Clinquant'));
        }

        $em->flush();

        return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }

    #[Route("/raccoon/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openRaccoonPouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'animalPouch/raccoon/#/open');

        $possibleItems = [
            'Beans',
            $rng->rngNextFromArray([ 'Baked Fish Fingers', 'Deep-fried Toad Legs' ]),
            'Trout Yogurt',
            'Caramel-covered Popcorn',
            $rng->rngNextFromArray([ 'Instant Ramen (Dry)', 'Paper Bag' ]),
            $rng->rngNextFromArray([ 'Mixed Nut Brittle', 'Berry Muffin' ]),
        ];

        $spice = $inventory->getSpice();
        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = $rng->rngNextSubsetFromArray($possibleItems, 3);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

            if($spice)
            {
                $item->setSpice($spice);
                $spice = null;
            }
        }

        $em->flush();

        if($spice)
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . ' - and they\'re all so ' . $spice->getName() . '!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }
}
