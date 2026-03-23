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

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\UserQuestRepository;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/flowerbomb")]
class FlowerbombController
{
    #[Route("/{inventory}/toss", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        InventoryService $inventoryService, HotPotatoService $hotPotatoService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'flowerbomb/#/toss');

        $lastFlowerBombWasNarcissistic = UserQuestRepository::findOrCreate($em, $user, 'Last Flowerbomb was Narcissus', true);
        $numberOfTosses = HotPotatoService::countTosses($inventory);
        $isNarcissusBomb = $numberOfTosses === 0;

        if($isNarcissusBomb && $lastFlowerBombWasNarcissistic->getValue())
            return $hotPotatoService->tossItem($inventory);

        if($rng->rngNextInt(1, 100) > 10 + $numberOfTosses * 5)
            return $hotPotatoService->tossItem($inventory);

        $possibleFlowers = $isNarcissusBomb
            ? [ 'Narcissus' ]
            : [
                'Agrimony',
                'Bird\'s-foot Trefoil',
                'Coriander Flower',
                'Green Carnation',
                'Iris',
                'Purple Violet',
                'Red Clover',
                'Viscaria',
                'Witch-hazel',
                'Wheat Flower',
                'Rice Flower',
                'Merigold',
            ]
        ;

        $lastFlowerBombWasNarcissistic->setValue($isNarcissusBomb);

        for($i = 0; $i < 10 + $numberOfTosses; $i++)
        {
            $flower = $rng->rngNextFromArray($possibleFlowers);
            $inventoryService->receiveItem($flower, $user, $inventory->getCreatedBy(), 'This exploded out of a Flowerbomb.', $inventory->getLocation());
        }

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You get ready to toss the Flowerbomb, but it explodes in your hands! Flowers go flying everywhere! (Mostly into your house.)', [ 'itemDeleted' => true ]);
    }
}
