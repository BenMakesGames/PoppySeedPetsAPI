<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/spiceRack")
 */
class SpiceRackController extends AbstractController
{
    /**
     * @Route("/{inventory}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function loot(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'spiceRack/#/loot');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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
