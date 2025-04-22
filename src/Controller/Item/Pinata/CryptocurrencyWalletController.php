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
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/cryptocurrencyWallet")]
class CryptocurrencyWalletController extends AbstractController
{
    #[Route("/{inventory}/unlock", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, TransactionService $transactionService,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cryptocurrencyWallet/#/unlock');

        $key = InventoryRepository::findOneToConsume($em, $user, 'Password');

        if(!$key)
            throw new PSPNotFoundException('It\'s locked! (It\'s got a little lock on it, and everything!) You\'ll need a Password to open it...');

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        if($rng->rngNextInt(1, 4) === 1)
        {
            $inventoryService->receiveItem('Magic Smoke', $user, $user, 'Escaped from a Cryptocurrency Wallet, ruining it.', $inventory->getLocation());

            $message = 'While waiting for the wallet to decrypt, some Magic Smoke escapes from it! Noooooo!';
        }
        else
        {
            $moneys = $rng->rngNextInt($rng->rngNextInt(5, 15), $rng->rngNextInt(20, $rng->rngNextInt(25, 95)));

            $transactionService->getMoney($user, $moneys, 'Found inside a ' . $inventory->getItem()->getName() . '.');

            $message = 'You decrypt the wallet, receiving ' . $moneys . '~~m~~.';
        }

        $em->remove($inventory);
        $em->remove($key);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
