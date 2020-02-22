<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/linensAndThings")
 */
class LinensController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/rummage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rummageThroughLinens(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'linensAndThings/#/rummage');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $numberOfCloth = mt_rand(2, 3);

        for($i = 0; $i < $numberOfCloth; $i++)
            $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' found this in a pile of Linens and Things.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You rummaged around in the pile, and pulled out ' . $numberOfCloth . ' pieces of good Cloth.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
