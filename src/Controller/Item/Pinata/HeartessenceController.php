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

#[Route("/item/heartessence")]
class HeartessenceController extends AbstractController
{
    #[Route("/{inventory}/quintessence", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/quintessence');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 3; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and pulls itself apart into three motes of Quintessence!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/magicSmoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMagicSmoke(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/magicSmoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 3; $i++)
            $inventoryService->receiveItem('Magic Smoke', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and pulls itself apart into three wisps of Magic Smoke!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/hatBox", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getHatBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartessence/#/magicSmoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Hat Box', $user, $user, $user->getName() . ' got this from a Heartessence.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartessence twists, and folds itself into a Hat Box!', [ 'itemDeleted' => true ]);
    }
}
