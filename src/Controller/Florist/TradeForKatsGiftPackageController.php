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


namespace App\Controller\Florist;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Service\FloristService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/florist")]
class TradeForKatsGiftPackageController
{
    #[Route("/tradeForGiftPackage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeTrade(
        InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em, TraderService $traderService, UserStatsService $userStatsService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $quantities = $inventoryRepository->getInventoryQuantities($user, LocationEnum::HOME, 'name');

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Monday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Tuesday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Wednesday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Thursday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Friday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Saturday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Sunday Coin'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Kat\'s Gift Package'), 1),
            ],
            '',
            $user,
            $quantities,
            true
        );

        $traderService->makeExchange($user, $exchange, LocationEnum::HOME, 1, 'Received by trading with the florist, Kat.');
        $userStatsService->incrementStat($user, 'Traded for Kat\'s Gift Package');

        $em->flush();

        return $responseService->success();
    }
}
