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


namespace App\Controller\MarketBid;

use App\Entity\Inventory;
use App\Entity\InventoryForSale;
use App\Entity\MarketBid;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ItemRepository;
use App\Functions\MarketListingRepository;
use App\Repository\MarketBidRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/marketBid")]
class CreateBidController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["POST"])]
    public function createBid(
        Request $request, ResponseService $responseService, TransactionService $transactionService,
        MarketBidRepository $marketBidRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Market))
            throw new PSPNotUnlockedException('Market');

        $itemsAtHome = InventoryService::countTotalInventory($em, $user, LocationEnum::HOME);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            $location = LocationEnum::HOME;
        else
        {
            $location = $request->request->getInt('location', LocationEnum::HOME);

            if(!LocationEnum::isAValue($location))
                throw new PSPFormValidationException('You must select a location for the item to go to.');
        }

        if($itemsAtHome >= User::MAX_HOUSE_INVENTORY)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                throw new PSPInvalidOperationException('Your house is already overflowing with items! You\'ll need to clear some out before you can create any new bids.');

            $itemsInBasement = InventoryService::countTotalInventory($em, $user, LocationEnum::BASEMENT);

            if($itemsInBasement >= User::MAX_BASEMENT_INVENTORY)
                throw new PSPInvalidOperationException('Your house and basement are already overflowing with items! You\'ll need to clear some space before you can create any new bids.');
        }

        $itemId = $request->request->getInt('item');

        if($itemId <= 0)
            throw new PSPFormValidationException('You must select an item to bid on.');

        $item = ItemRepository::findOneById($em, $itemId);

        $quantity = $request->request->getInt('quantity');

        if($quantity < 1)
            throw new PSPFormValidationException('You can\'t bid on ' . $quantity . ' items! That\'s just silly!');

        $currentQuantity = $marketBidRepository->getTotalQuantity($user);

        if($currentQuantity + $quantity > $user->getMaxMarketBids())
            throw new PSPInvalidOperationException('You can only have bids out on ' . $user->getMaxMarketBids() . ' items at a time.');

        $bid = $request->request->getInt('bid');

        if($bid < 2)
            throw new PSPFormValidationException('No one can sell an item for less than 2~~m~~, so bidding for less than that wouldn\'t ever work :P');

        if($bid * $quantity > $user->getMoneys())
            throw new PSPNotEnoughCurrencyException(($bid * $quantity) . '~~m~~', $user->getMoneys() . '~~m~~');

        $listing = MarketListingRepository::findMarketListingForItem($em, $itemId);

        if($listing && InventoryForSale::calculateBuyPrice($listing->getMinimumSellPrice()) <= $bid)
            throw new PSPInvalidOperationException('Someone is currently selling ' . $item->getName() . ' for less than or equal to that price! [Go buy those up, first!](/market?filter.name=' . urlencode($item->getName()) . ')');

        $transactionService->spendMoney($user, $bid * $quantity, 'Money put in for a bid on ' . $quantity . 'x ' . $item->getName() . '.', false);

        $myBid = (new MarketBid())
            ->setUser($user)
            ->setBid($bid)
            ->setQuantity($quantity)
            ->setItem($item)
            ->setTargetLocation($location)
        ;

        $em->persist($myBid);

        $em->flush();

        return $responseService->success($myBid, [ SerializationGroupEnum::MY_MARKET_BIDS ]);
    }
}
