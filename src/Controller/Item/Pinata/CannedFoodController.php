<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\RecyclingService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item/cannedFood")
 */
class CannedFoodController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $this->validateInventory($inventory, 'cannedFood/#/open');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $cansOpened = $userStatsRepository->findOrCreate($user, UserStatEnum::CANS_OF_FOOD_OPENED);

        if($cansOpened->getValue() > 2 && $squirrel3->rngNextInt(1, 50) === 1)
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

        RecyclingService::giveRecyclingPoints($user, 1);

        $cansOpened->increaseValue(1);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
