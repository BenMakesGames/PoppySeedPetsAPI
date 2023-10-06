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
 * @Route("/item/awaOdori")
 */
class AwaOdoriController extends AbstractController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'awaOdori/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $newInventory = [
            $inventoryService->receiveItem('Behatting Scroll', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Amigasa', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Music Note', $user, $user, $comment, $location, $lockedToOwner),
        ];

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $user, $inventory, $newInventory, $responseService, $em);
    }

}