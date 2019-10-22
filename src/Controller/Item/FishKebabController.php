<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fishKebab")
 */
class FishKebabController extends PoppySeedPetsItemController
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
        $this->validateInventory($inventory, 'fishKebab/#/takeApart');

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location);
        $inventoryService->receiveItem('Fish', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location);
        $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Fishkebab.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take the Fishkebab apart, receiving three pieces of Fish, and a Crooked Stick.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}