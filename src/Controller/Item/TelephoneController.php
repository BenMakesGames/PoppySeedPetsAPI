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
use App\Enum\LocationEnum;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/telephone")]
class TelephoneController
{
    #[Route("/{inventory}/pizza", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pizza(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        TransactionService $transactionService, IRandom $rng,
        InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'telephone/#/pizza');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $orderedDeliveryFood = UserQuestRepository::findOrCreate($em, $user, 'Ordered Delivery Food', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $orderedDeliveryFood->getValue())
            return $responseService->itemActionSuccess('You can only order delivery food once per day. (More than that is just irresponsible!)');

        $orderedDeliveryFood->setValue($today);

        if($user->getMoneys() < 45)
            throw new PSPNotEnoughCurrencyException('45~~m~~', $user->getMoneys());

        $transactionService->spendMoney($user, 45, 'Got delivery pizza');

        $pizzas = $rng->rngNextSubsetFromArray([
            'Slice of Cheese Pizza',
            'Slice of Chicken BBQ Pizza',
            'Slice of Mixed Mushroom Pizza',
            'Slice of Pineapple Pizza',
            'Slice of Spicy Calamari Pizza',
        ], 3);

        sort($pizzas);

        foreach($pizzas as $pizza)
            $inventoryService->receiveItem($pizza, $user, $user, 'You ordered this pizza over the telephone.', LocationEnum::HOME);

        $em->flush();

        return $responseService->itemActionSuccess('You ordered some pizza over the telephone. It\'s on its way-- no, wait, it\'s already here! (So speedy and so smart!)');
    }
}