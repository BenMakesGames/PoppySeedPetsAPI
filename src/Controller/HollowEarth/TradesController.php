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


namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/hollowEarth")]
class TradesController
{
    #[Route("/trades", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getTrades(
        ResponseService $responseService, HollowEarthService $hollowEarthService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $player = $user->getHollowEarthPlayer()
            ?? throw new PSPInvalidOperationException('You\'re not currently exploring the Hollow Earth!');

        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new PSPInvalidOperationException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t trade while you\'re moving!');

        $trades = $hollowEarthService->getTrades($player);

        return $responseService->success($trades);
    }

    #[Route("/trades/{tradeId}/exchange", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeExchange(
        string $tradeId, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        HollowEarthService $hollowEarthService, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $player = $user->getHollowEarthPlayer()
            ?? throw new PSPInvalidOperationException('You\'re not currently exploring the Hollow Earth!');

        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new PSPInvalidOperationException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t trade while you\'re moving!');

        $trade = $hollowEarthService->getTrade($player, $tradeId);

        if(!$trade)
            throw new PSPNotFoundException('No such trade exists...');

        $quantity = $request->request->getInt('quantity', 1);

        if($trade['maxQuantity'] < $quantity)
            throw new PSPInvalidOperationException('You do not have enough goods to make ' . $quantity . ' trade' . ($quantity == 1 ? '' : 's') . '; you can do up to ' . $trade['maxQuantity'] . ', at most.');

        $itemsAtHome = InventoryService::countTotalInventory($em, $user, LocationEnum::Home);

        $destination = LocationEnum::Home;

        if($itemsAtHome + $quantity > User::MaxHouseInventory)
        {
            if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            {
                $destination = LocationEnum::Basement;

                $itemsInBasement = InventoryService::countTotalInventory($em, $user, LocationEnum::Basement);

                if($itemsInBasement + $quantity > User::MaxBasementInventory)
                {
                    throw new PSPInvalidOperationException('There is not enough room in your house or basement for ' . $quantity . ' more items!');
                }
            }
            else
            {
                throw new PSPInvalidOperationException('There is not enough room in your house for ' . $quantity . ' more items!');
            }
        }

        $item = ItemRepository::findOneByName($em, $trade['item']['name']);

        for($i = 0; $i < $quantity; $i++)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' traded for this in the Hollow Earth.', $destination);

        if(array_key_exists('jade', $trade['cost'])) $player->increaseJade(-$trade['cost']['jade'] * $quantity);
        if(array_key_exists('incense', $trade['cost'])) $player->increaseIncense(-$trade['cost']['incense'] * $quantity);
        if(array_key_exists('amber', $trade['cost'])) $player->increaseAmber(-$trade['cost']['amber'] * $quantity);
        if(array_key_exists('salt', $trade['cost'])) $player->increaseSalt(-$trade['cost']['salt'] * $quantity);
        if(array_key_exists('fruit', $trade['cost'])) $player->increaseFruit(-$trade['cost']['fruit'] * $quantity);

        $em->flush();

        $trades = $hollowEarthService->getTrades($player);

        $destinationDescription = $destination === LocationEnum::Home
            ? 'your house'
            : 'your basement'
        ;

        $themOrIt = $quantity === 1 ? 'it' : 'them';

        $responseService->addFlashMessage('You traded for ' . $quantity . '× ' . $item->getName() . '. (Find ' . $themOrIt . ' in ' . $destinationDescription . '.)');

        return $responseService->success($trades);
    }
}
