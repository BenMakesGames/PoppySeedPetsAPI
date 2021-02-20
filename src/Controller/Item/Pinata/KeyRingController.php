<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/keyRing")
 */
class KeyRingController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/takeIron", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeIronKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'keyRing/#/takeIron');

        $user = $this->getUser();

        $inventoryService->receiveItem('Iron Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Iron Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Iron Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/takeSilver", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeSilverKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'keyRing/#/takeSilver');

        $user = $this->getUser();

        $inventoryService->receiveItem('Silver Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Silver Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Silver Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/takeGold", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeGoldKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'keyRing/#/takeGold');

        $user = $this->getUser();

        $inventoryService->receiveItem('Gold Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Gold Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Gold Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }
}