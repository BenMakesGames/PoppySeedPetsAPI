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

namespace App\Controller\Bookstore;

use App\Entity\Item;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BookstoreService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// allows player to buy books; inventory grows based on various criteria

#[Route("/bookstore")]
class BuyBook
{
    #[Route("/{item}/buy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buyBook(
        Item $item, BookstoreService $bookstoreService, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, TransactionService $transactionService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
            throw new PSPNotUnlockedException('Bookstore');

        $bookPrices = $bookstoreService->getAvailableBooks($user);
        $gamePrices = $bookstoreService->getAvailableGames($user);
        $cafePrices = $bookstoreService->getAvailableCafe($user);

        $allPrices = array_merge($bookPrices, $gamePrices, $cafePrices);

        if(!array_key_exists($item->getName(), $allPrices))
            throw new PSPFormValidationException('That item cannot be purchased.');

        if($user->getMoneys() < $allPrices[$item->getName()])
            throw new PSPNotEnoughCurrencyException($allPrices[$item->getName()] . '~~m~~', $user->getMoneys() . '~~m~~');

        $itemsAtHome = $inventoryService->countItemsInLocation($user, LocationEnum::Home);

        if($itemsAtHome >= User::MaxHouseInventory)
            throw new PSPFormValidationException('Your house is already overflowing with items! (The usual max is ' . User::MaxHouseInventory . ' items - you\'ve got ' . $itemsAtHome . '!)');

        $cost = $allPrices[$item->getName()];
        $transactionService->spendMoney($user, $cost, 'You bought ' . $item->getName() . ' from the Bookstore.');

        $inventoryService->receiveItem($item, $user, null, $user->getName() . ' bought this from the Book Store.', LocationEnum::Home, true);

        $em->flush();

        return $responseService->success();
    }
}
