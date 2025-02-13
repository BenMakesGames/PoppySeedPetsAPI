<?php
declare(strict_types=1);

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
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3,
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

        if($squirrel3->rngNextInt(1, 4) === 1)
        {
            $inventoryService->receiveItem('Magic Smoke', $user, $user, 'Escaped from a Cryptocurrency Wallet, ruining it.', $inventory->getLocation());

            $message = 'While waiting for the wallet to decrypt, some Magic Smoke escapes from it! Noooooo!';
        }
        else
        {
            $moneys = $squirrel3->rngNextInt($squirrel3->rngNextInt(5, 15), $squirrel3->rngNextInt(20, $squirrel3->rngNextInt(25, 95)));

            $transactionService->getMoney($user, $moneys, 'Found inside a ' . $inventory->getItem()->getName() . '.');

            $message = 'You decrypt the wallet, receiving ' . $moneys . '~~m~~.';
        }

        $em->remove($inventory);
        $em->remove($key);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
