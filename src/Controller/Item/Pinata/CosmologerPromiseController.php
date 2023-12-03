<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cosmologerPromise")
 */
class CosmologerPromiseController extends AbstractController
{
    #[Route("/{inventory}/secretSeashell", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSecretSeashell(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cosmologerPromise/#/secretSeashell');

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Secret Seashell', $user, $user, $user->getName() . ' got this from a Cosmologer\'s Promise.', $location, $locked);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("You call up the cosmologist who says an errand boy is on the way. Sure enough, a few minutes later, a young boy arrives and hands you a Secret Seashell.\n\nHe smiles silently, then runs off.", [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/alienTissue", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAlienTissue(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cosmologerPromise/#/alienTissue');

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Alien Tissue', $user, $user, $user->getName() . ' got this from a Cosmologer\'s Promise.', $location, $locked);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("You call up the cosmologist who says an errand boy is on the way. Sure enough, a few minutes later, a young boy arrives and hands you some Alien Tissue. (Gross.)\n\nBefore you can offer to let him wash his hands in your kitchen, he runs off.", [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/veryStrongbox", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cosmologerPromise/#/veryStrongbox');

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Very Strongbox', $user, $user, $user->getName() . ' got this from a Cosmologer\'s Promise.', $location, $locked);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("You call up the cosmologist who says an errand boy is on the way. Sure enough, a few minutes later, a young boy arrives, panting heavily.\n\nHe drops a Very Strongbox on the ground at your feet, gives a silent nod, then runs off.", [ 'itemDeleted' => true ]);
    }
}
