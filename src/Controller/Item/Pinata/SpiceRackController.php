<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/spiceRack")
 */
class SpiceRackController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function loot(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        $this->validateInventory($inventory, 'spiceRack/#/loot');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $spices = $squirrel3->rngNextSubsetFromArray([
            'Cheesy Flakes',
            'Fool\'s Spice',
            'Freshly-squeezed Fish Oil',
            'Nutmeg',
            'Onion Powder',
            'Spicy Spice',
            'Spider Roe',
            'Artificial Grape Extract',
        ], 3);

        foreach($spices as $spice)
            $inventoryService->receiveItem($spice, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You raid the spice rack, finding ' . ArrayFunctions::list_nice($spices) . '!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
