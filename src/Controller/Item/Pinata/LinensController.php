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
use App\Entity\Trader;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/linensAndThings")]
class LinensController
{
    #[Route("/{inventory}/rummage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function rummageThroughLinens(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'linensAndThings/#/rummage');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $baseNumberOfCloth = $rng->rngNextInt(1, 2);

        $extraItem = $rng->rngNextFromArray([ 'White Cloth', 'Super-wrinkled Cloth' ]);

        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        for($i = 0; $i < $baseNumberOfCloth; $i++)
            $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        if($extraItem === 'Super-wrinkled Cloth')
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $baseNumberOfCloth . ' ' . ($baseNumberOfCloth === 1 ? 'piece' : 'pieces') . ' of good cloth... and 1 piece of Super-wrinkled Cloth...', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . ($baseNumberOfCloth + 1) . ' pieces of good cloth...', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/giveToTrader", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveToTrader(
        Inventory $inventory, ResponseService $responseService, IRandom $rng,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'linensAndThings/#/giveToTrader');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $trader = $em->getRepository(Trader::class)->findOneBy([ 'user' => $user->getId() ]);

        if(!$trader)
            throw new PSPInvalidOperationException('You should probably go visit the Trader first... at least once...');

        TraderService::recolorTrader($rng, $trader);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($trader->getName() . ' thanks you for the new clothes, and changes into them immediately.', [ 'itemDeleted' => true ]);
    }
}
