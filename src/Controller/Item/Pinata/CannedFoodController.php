<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'cannedFood/#/open');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $item = ArrayFunctions::pick_one([
            'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
            'Tomato', 'Corn', 'Fish', 'Beans', 'Creamed Corn',
            'Fermented Fish', 'Coffee Beans',
            'Tomato Soup', '"Chicken" Noodle Soup', 'Minestrone',
        ]);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a can. A Canned Food can.', $location, $lockedToOwner);

        $user->increaseRecyclePoints(1);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the can; it has ' . $item . ' inside! (You also recycle the can, and get 1♺. Woo.)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
