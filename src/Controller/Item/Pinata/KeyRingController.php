<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/keyRing")]
class KeyRingController extends AbstractController
{
    #[Route("/{inventory}/takeIron", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeIronKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'keyRing/#/takeIron');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventoryService->receiveItem('Iron Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Iron Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Iron Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/takeSilver", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeSilverKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'keyRing/#/takeSilver');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventoryService->receiveItem('Silver Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Silver Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Silver Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/takeGold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeGoldKeys(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'keyRing/#/takeGold');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventoryService->receiveItem('Gold Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Gold Key', $user, $user, $user->getName() . ' pulled this off a Key Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull two Gold Keys off the ring. Apparently, despite the graphic, that\'s all there was.', [ 'itemDeleted' => true ]);
    }
}