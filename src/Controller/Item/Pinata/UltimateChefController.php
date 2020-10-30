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
 * @Route("/item/ultimateChef")
 */
class UltimateChefController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'ultimateChef/#/read');

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Yes, Chef!', $user, $user, 'You tried to read the classic book "Ultimate Chef", but it somehow unfolded itself into this!', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the book, expecting to see, you know, pages of a book, but instead it somehow unfolds into a chef hat!' , [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
