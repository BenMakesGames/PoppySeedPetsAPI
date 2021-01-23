<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\TraderRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/barnacles")
 */
class BarnaclesController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestBarnacles(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        $this->validateInventory($inventory, 'barnacles/#/harvest');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $numberOfItems = $squirrel3->rngNextInt(1, 2);
        $loot = [];

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $itemName = $squirrel3->rngNextFromArray([ 'Egg', 'Egg', 'Feathers' ]);

            $loot[] = $itemName;

            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in a clump of Barnacles.', $location, $lockedToOwner);
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('You pry open the Barnacles, finding ' . ArrayFunctions::list_nice($loot) . '.', [ 'itemDeleted' => true ]);
    }
}
