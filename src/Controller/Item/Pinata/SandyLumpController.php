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

#[Route("/item/sandyLump")]
class SandyLumpController
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

        ItemControllerHelpers::validateInventory($user, $lump, 'sandyLump/#/clean');

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        if($rng->rngNextInt(1, 25) === 1)
        {
            $item = $rng->rngNextFromArray([
                'Secret Seashell',
                $rng->rngNextFromArray([ 'Striped Microcline', 'Blackonite' ]),
                'Dino Skull',
                'Key Ring',
                $rng->rngNextFromArray([ 'Meteorite', 'Species Transmigration Serum' ]),
            ]);
        }
        else
        {
            $item = $rng->rngNextFromArray([
                'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore',
                'Silica Grounds', 'Silica Grounds', 'Sand Dollar',
                'Talon',
                'Fish', 'Mermaid Egg', 'Mermaid Egg', 'Seaweed',
            ]);
        }

        $itemObject = ItemRepository::findOneByName($em, $item);

        $userStatsRepository->incrementStat($user, 'Cleaned a ' . $lump->getItem()->getName());

        if($item === 'Silica Grounds')
        {
            $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in Silica Grounds.', $location, $lockedToOwner);
            $message = 'You begin the clean the object, but realize it\'s just Silica Grounds all the way through! (Well... at least you got some Silica Grounds, I guess?)';
        }
        else
        {
            $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in Silica Grounds.', $location, $lockedToOwner);
            $message = 'You clean off the object, which reveals itself to be ' . $itemObject->getNameWithArticle() . '!';
        }

        $em->remove($lump);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
