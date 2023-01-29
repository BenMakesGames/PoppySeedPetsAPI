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
 * @Route("/item/laserGuitar")
 */
class LaserGuitarController extends AbstractController
{
    /**
     * @Route("/{inventory}/overload", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function overload(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'laserGuitar/#/overload');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Magic Smoke', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You put the Laser Guitar into overload, and take a few steps back. A few seconds later it explodes into a spectacular show of light, music, and plastic shrapnel. After everything settles down, you collect the remains...', [ 'itemDeleted' => true ]);
    }
}
