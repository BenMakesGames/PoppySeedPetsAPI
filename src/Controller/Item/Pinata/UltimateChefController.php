<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/ultimateChef")]
class UltimateChefController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ultimateChef/#/read');

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Yes, Chef!', $user, $user, 'You tried to read the classic book "Ultimate Chef", but it somehow unfolded itself into this!', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the book, expecting to see, you know, pages of a book, but instead it somehow unfolds into a chef hat!' , [ 'itemDeleted' => true ]);
    }
}
