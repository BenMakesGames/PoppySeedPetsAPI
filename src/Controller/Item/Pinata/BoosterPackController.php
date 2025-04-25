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
use App\Entity\ItemGroup;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/boosterPack")]
class BoosterPackController
{
    private const array SET_ITEM_GROUP_NAMES = [
        'one' => 'Hollow Earth Booster Pack',
        'two' => 'Community Booster Pack',
    ];

    #[Route("/{set}/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBoosterPackOne(
        string $set, Inventory $inventory,

        ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'boosterPack/' . $set . '/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $itemGroupNameBase = self::SET_ITEM_GROUP_NAMES[$set];

        $commons = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Common' ]);
        $uncommons = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Uncommon' ]);
        $rares = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Rare' ]);

        $tiles = [
            InventoryService::getRandomItemFromItemGroup($rng, $commons),
            InventoryService::getRandomItemFromItemGroup($rng, $commons),
            InventoryService::getRandomItemFromItemGroup($rng, $uncommons),
            InventoryService::getRandomItemFromItemGroup($rng, $rares)
        ];

        $tileNames = [
            $tiles[0]->getName() . ' (☆)',
            $tiles[1]->getName() . ' (☆)',
            $tiles[2]->getName() . ' (☆☆)',
            $tiles[3]->getName() . ' (☆☆☆)',
        ];

        foreach($tiles as $tile)
            $inventoryService->receiveItem($tile, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the ' . $inventory->getItem()->getName() . ', receiving ' . ArrayFunctions::list_nice($tileNames) . '!', [ 'itemDeleted' => true ]);
    }
}
