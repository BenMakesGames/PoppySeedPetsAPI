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
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/wrappedSword")]
class WrappedSwordController
{
    #[Route("/{inventory}/unwrap", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function unwrap(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        IRandom $rng, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wrappedSword/#/unwrap');

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $swordItem = ItemRepository::findOneByName($em, $rng->rngNextFromArray([
            'Rapier',
            'Iron Sword',
            'Brute Force',
            $rng->rngNextFromArray([ 'Silver Keyblade', 'Gold Keyblade' ]),
            'Wooden Sword'
        ]));

        $sword = $inventoryService->receiveItem($swordItem, $user, $user, $user->getName() . ' unwrapped a Wrapped Sword, revealing this!', $location, $lockedToOwner);
        $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' unwrapped a Wrapped Sword; this was the wrapping.', $location, $lockedToOwner);

        if($sword->getSpice() == null && $rng->rngNextBool())
        {
            $spice = SpiceRepository::findOneByName($em, $rng->rngNextFromArray([
                'Spicy',
                'Ducky',
                'Nutmeg-laden',
                'Tropical',
                'Buttery',
                'Grape?',
                'Rain-scented',
                'Juniper',
            ]));

            $sword->setSpice($spice);
        }
        else
        {
            $bonus = EnchantmentRepository::findOneByName($em, $rng->rngNextFromArray([
                'Bright',
                'Spider\'s',
                'of the Moon',
                'Enchantress\'s',
                'Explosive', // firework
                'Bezeling',
                'Climbing', // dragon vase
                'Dancing',
                'Fisherman\'s',
                'Fluffmonger\'s',
                'Glowing',
                'of the Unicorn',
            ]));

            $sword->setEnchantment($bonus);
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You unwrap the wrapped sword... it\'s ' . $sword->getItem()->getNameWithArticle() . '! (You keep the cloth, too, of course!)', [ 'itemDeleted' => true ]);
    }
}
