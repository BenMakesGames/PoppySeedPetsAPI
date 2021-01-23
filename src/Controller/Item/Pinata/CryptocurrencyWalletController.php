<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cryptocurrencyWallet")
 */
class CryptocurrencyWalletController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/unlock", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository,
        TransactionService $transactionService, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'cryptocurrencyWallet/#/unlock');

        $user = $this->getUser();

        $key = $inventoryRepository->findOneToConsume($user, 'Password');

        if(!$key)
            throw new UnprocessableEntityHttpException('It\'s locked! (It\'s got a little lock on it, and everything!) You\'ll need a Password to open it...');

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

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
