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

namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/scroll")]
class RichesController extends AbstractController
{
    #[Route("/minorRiches/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeMinorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/minorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = $rng->rngNextInt(30, 50);

        $item = $rng->rngNextFromArray([ 'Bag of Beans', 'Moon Pearl', 'Limestone' ]);
        $location = $inventory->getLocation();

        if($rng->rngNextInt(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Minor Scroll of Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Minor Scroll of Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/majorRiches/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeMajorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/majorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = $rng->rngNextInt(60, 100);

        $item = $rng->rngNextFromArray([ 'Little Strongbox', 'Striped Microcline', 'Firestone', 'Blackonite' ]);
        $location = $inventory->getLocation();

        if($rng->rngNextInt(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
