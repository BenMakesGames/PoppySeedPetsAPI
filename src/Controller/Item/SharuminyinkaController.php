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
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/sharuminyinka")]
class SharuminyinkaController extends AbstractController
{
    #[Route("/{inventory}/createHope", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function createHope(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService,
        EntityManagerInterface $em, InventoryRepository $inventoryRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sharuminyinka/#/createHope');

        $location = $inventory->getLocation();

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Poker'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Spider'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Feathers'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Sharuminyinka\'s Hope'), 1),
            ],
            '',
            $user,
            []
        );

        if(!$traderService->userCanMakeExchange($user, $exchange, $location))
        {
            return $responseService->itemActionSuccess('You need a Poker, Spider, Feathers, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, $location, 1, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Sharuminyinka\'s Hope.');
        }
    }
    #[Route("/{inventory}/createMemory", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function createMemory(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sharuminyinka/#/createMemory');

        $location = $inventory->getLocation();

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Crazy-hot Torch'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Blackonite'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'String'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Tig\'s Memory'), 1),
            ],
            '',
            $user,
            []
        );

        if(!$traderService->userCanMakeExchange($user, $exchange, $location))
        {
            return $responseService->itemActionSuccess('You need a Crazy-hot Torch, Blackonite, String, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, $location, 1, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Tig\'s Memory.');
        }
    }
}
