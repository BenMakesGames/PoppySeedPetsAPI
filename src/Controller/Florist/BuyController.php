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

use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Service\FloristService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/florist")]
class BuyController
{
    #[Route("/buy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buyFlowerbomb(
        Request $request, FloristService $floristService,
        InventoryService $inventoryService, ResponseService $responseService, UserStatsService $userStatsRepository,
        EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $offers = $floristService->getInventory($user);
        $userPickName = $request->request->get('item');

        $userPick = ArrayFunctions::find_one($offers, fn($o) => $o['item']['name'] === $userPickName);

        if(!$userPick)
            throw new PSPFormValidationException('That item is not available... (maybe reload the page and try again??)');

        if($user->getMoneys() < $userPick['cost'])
            throw new PSPNotEnoughCurrencyException($userPick['cost'] . '~~m~~', $user->getMoneys() . '~~m~~');

        $transactionService->spendMoney($user, $userPick['cost'], 'Purchased a ' . $userPick['item']['name'] . ' at The Florist.');

        $inventoryService->receiveItem($userPick['item']['name'], $user, $user, $user->getName() . ' bought this at The Florist\'s.', LocationEnum::Home, true);

        $statName = $userPick['item']['name'] . 's Purchased';

        $stat = $userStatsRepository->incrementStat($user, $statName);

        if($userPick['item']['name'] === 'Flowerbomb' && $stat->getValue() === 1)
            $inventoryService->receiveItem('Book of Flowers', $user, $user, 'This was delivered to you from The Florist\'s.', LocationEnum::Home, true);

        $em->flush();

        return $responseService->success();
    }
}
