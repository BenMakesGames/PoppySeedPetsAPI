<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/item/cannedFood")]
class CannedFoodController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cannedFood/#/open');

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $cansOpened = $userStatsRepository->incrementStat($user, UserStatEnum::CANS_OF_FOOD_OPENED);

        if($cansOpened->getValue() > 3 && $squirrel3->rngNextInt(1, 50) === 1)
        {
            $worms = $squirrel3->rngNextInt(4, 12);

            for($i = 0; $i < $worms; $i++)
                $inventoryService->receiveItem('Worms', $user, $user, $user->getName() . ' found this in a can. A Canned Food can. Of worms.', $location, $lockedToOwner);

            $message = 'You open the can - AGK! IT WAS A CAN OF WORMS! (Despite this, you do also recycle the can, and get 1♺. Woo?)';
        }
        else
        {
            $item = $squirrel3->rngNextFromArray([
                'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
                'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
                'Fermented Fish', 'Coffee Beans',
                'Tomato Soup', '"Chicken" Noodle Soup', 'Minestrone',
            ]);

            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a can. A Canned Food can.', $location, $lockedToOwner)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You open the can; it has ' . $item . ' inside! (You also recycle the can, and get 1♺. Woo.)';
        }

        $transactionService->getRecyclingPoints($user, 1, 'You recycled the can from some Canned Food.');

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
