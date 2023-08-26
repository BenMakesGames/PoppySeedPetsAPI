<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class RichesController extends AbstractController
{
    /**
     * @Route("/minorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMinorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService,
        Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/minorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = $squirrel3->rngNextInt(30, 50);

        $item = $squirrel3->rngNextFromArray([ 'Little Strongbox', 'Bag of Beans' ]);
        $location = $inventory->getLocation();

        if($squirrel3->rngNextInt(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Minor Scroll of Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Minor Scroll of Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/majorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMajorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/majorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = $squirrel3->rngNextInt(60, 100);

        $item = $squirrel3->rngNextFromArray([ 'Striped Microcline', 'Firestone', 'Moon Pearl', 'Blackonite' ]);
        $location = $inventory->getLocation();

        if($squirrel3->rngNextInt(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
