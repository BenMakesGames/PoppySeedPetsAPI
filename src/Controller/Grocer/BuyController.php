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

namespace App\Controller\Grocer;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\GrocerService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/grocer')]
class BuyController
{
    #[Route('/buy', methods: ['POST'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buy(
        Request $request, ResponseService $responseService, GrocerService $grocerService,
        TransactionService $transactionService, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, UserAccessor $userAccessor
    ): JsonResponse
    {
        $buyTo = $request->request->getInt('location');
        $payWith = strtolower($request->request->getAlpha('payWith', 'moneys'));

        if($buyTo !== LocationEnum::Home && $buyTo !== LocationEnum::Basement)
            throw new PSPFormValidationException('You must select a location to put the purchased items.');

        if($payWith !== 'moneys' && $payWith !== 'recycling')
            throw new PSPFormValidationException('You must choose whether to pay with moneys or with recycling points.');

        $inventory = $grocerService->getInventory();

        $buyingInventory = [];
        $totalQuantity = 0;
        $totalCost = 0;

        foreach($inventory as $i)
        {
            $itemName = $i['item']['name'];

            if($request->request->has($itemName))
            {
                $quantity = $request->request->getInt($itemName);

                if($quantity > 0)
                {
                    $totalQuantity += $quantity;
                    $totalCost += $i[$payWith . 'Cost'] * $quantity;

                    if(!array_key_exists($itemName, $buyingInventory))
                        $buyingInventory[$itemName] = $quantity;
                    else
                        $buyingInventory[$itemName] += $quantity;
                }
            }
        }

        $user = $userAccessor->getUserOrThrow();
        $now = new \DateTimeImmutable();

        $grocerItemsQuantity = UserQuestRepository::findOrCreate($em, $user, 'Grocer Items Purchased Quantity', 0);
        $grocerItemsDay = UserQuestRepository::findOrCreate($em, $user, 'Grocer Items Purchased Date', $now->format('Y-m-d'));

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $maxCanPurchase = GrocerService::MaxCanPurchasePerDay - $grocerItemsQuantity->getValue();
        else
            $maxCanPurchase = GrocerService::MaxCanPurchasePerDay;

        if($totalQuantity > $maxCanPurchase)
            throw new PSPInvalidOperationException('Only ' . GrocerService::MaxCanPurchasePerDay . ' items per day, please.');

        if(count($buyingInventory) === 0)
            throw new PSPFormValidationException('Did you forget to select something to buy?');

        $existingInventoryCount = InventoryService::countTotalInventory($em, $user, $buyTo);
        $maxInventory = $buyTo === LocationEnum::Basement ? User::MaxBasementInventory : User::MaxHouseInventory;

        if($existingInventoryCount + $totalQuantity > $maxInventory)
        {
            if($buyTo === LocationEnum::Home)
                throw new PSPInvalidOperationException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your House.');
            else
                throw new PSPInvalidOperationException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your Basement.');
        }

        if($payWith === 'moneys')
        {
            if($totalCost > $user->getMoneys())
                throw new PSPNotEnoughCurrencyException($totalCost . '~~m~~', $user->getMoneys() . '~~m~~');

            $transactionService->spendMoney($user, $totalCost, 'Purchased ' . $totalQuantity . ' thing' . ($totalQuantity === 1 ? '' : 's') . ' from the Grocer.', true, [ 'Grocer' ]);
        }
        else
        {
            if($totalCost > $user->getRecyclePoints())
                throw new PSPNotEnoughCurrencyException($totalCost . '♺', $user->getRecyclePoints() . '♺');

            $transactionService->spendRecyclingPoints($user, $totalCost, 'Purchased ' . $totalQuantity . ' thing' . ($totalQuantity === 1 ? '' : 's') . ' from the Grocer.', [ 'Grocer' ]);
        }

        foreach($buyingInventory as $itemName=>$quantity)
        {
            for($i = 0; $i < $quantity; $i++)
            {
                $item = ItemRepository::findOneByName($em, $itemName);

                $newInventory = (new Inventory(owner: $user, item: $item))
                    ->setLocation($buyTo)
                    ->setLockedToOwner(true)
                    ->addComment($user->getName() . ' bought this from the Grocery Store.')
                ;

                $em->persist($newInventory);
            }
        }

        $userStatsRepository->incrementStat($user, 'Items Purchased from Grocer', $totalQuantity);

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $grocerItemsQuantity->setValue($grocerItemsQuantity->getValue() + $totalQuantity);
        else
        {
            $grocerItemsDay->setValue($now->format('Y-m-d'));
            $grocerItemsQuantity->setValue($totalQuantity);
        }

        $em->flush();

        $currency = $payWith === 'moneys' ? '~~m~~' : ' recycling points';

        $responseService->addFlashMessage($totalQuantity . ' ' . ($totalQuantity === 1 ? 'item was' : 'items were') . ' purchased for ' . $totalCost . $currency . '. ' . ($totalQuantity === 1 ? 'It' : 'They') . ' can be found in your ' . ($buyTo === LocationEnum::Home ? 'House' : 'Basement') . '.');

        return $responseService->success([
            'maxPerDay' => GrocerService::MaxCanPurchasePerDay,
            'maxRemainingToday' => $maxCanPurchase - $totalQuantity,
        ]);
    }
}