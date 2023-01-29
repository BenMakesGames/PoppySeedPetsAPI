<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fishKebab")
 */
class FishKebabController extends AbstractController
{
    /**
     * @Route("/{inventory}/takeApart", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'fishKebab/#/takeApart');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You take the Fishkebab apart, receiving three pieces of Fish, and a Crooked Stick.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
