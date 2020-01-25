<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository,
        TransactionService $transactionService
    )
    {
        $this->validateInventory($inventory, 'cryptocurrencyWallet/#/unlock');

        $user = $this->getUser();

        $key = $inventoryRepository->findOneToConsume($user, 'Password');

        if(!$key)
            throw new UnprocessableEntityHttpException('It\'s locked! (It\'s got a little lock on it, and everything!) You\'ll need a Password to open it...');

        $moneys = mt_rand(mt_rand(5, 15), mt_rand(20, mt_rand(25, 95)));

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $transactionService->getMoney($user, $moneys, 'Found inside a ' . $inventory->getItem()->getName() . '.');

        $em->remove($inventory);
        $em->remove($key);

        $em->flush();

        return $responseService->itemActionSuccess('You decrypt the wallet, receiving ' . $moneys . '~~m~~.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}