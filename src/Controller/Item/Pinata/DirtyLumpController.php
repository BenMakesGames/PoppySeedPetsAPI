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
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/dirtyLump")]
class DirtyLumpController
{
    #[Route("/{lump}/clean", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function clean(
        Inventory $lump, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $lump, 'dirtyLump/#/clean');

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        $item = $rng->rngNextFromArray([
            'Secret Seashell',
            'Striped Microcline',
            'Blackonite',
            $rng->rngNextFromArray([ 'Meteorite', 'Species Transmigration Serum' ]),
            'Century Egg',
            'Digital Camera',
            'Beta Bug',
            'White Bow',
            'Fish Bones',
            'Worms',
            $rng->rngNextFromArray([ 'Potion of Stealth', 'Werebane' ]),
            'Propeller Beanie',
            'Rusted, Busted Mechanism',
            $rng->rngNextFromArray([ 'Tile: Hidden Alcove', 'Tile: Sand Worm!' ]),
            'Tower Chest',
            'Major Scroll of Riches',
            'Sand-covered... Something',
            'Monster Box',
            'Lunchbox Paint',
            $rng->rngNextFromArray([ 'Flowerbomb', 'Giant Turkey Leg' ]),
        ]);

        $itemObject = ItemRepository::findOneByName($em, $item);

        $userStatsRepository->incrementStat($user, 'Cleaned a ' . $lump->getItem()->getName());

        $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in dirt.', $location, $lockedToOwner);
        $message = 'You clean off the object, which reveals itself to be ' . $itemObject->getNameWithArticle() . '!';

        $em->remove($lump);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
