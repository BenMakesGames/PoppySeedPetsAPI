<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fairyRing")
 */
class FairyRingController extends AbstractController
{
    /**
     * @Route("/{inventory}/takeApart", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeApart(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository, Squirrel3 $squirrel3,
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'fairyRing/#/takeApart');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $inventory->changeItem($itemRepository->findOneByName('Gold Ring'));

        $inventoryService->receiveItem('Wings', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled these off a Fairy Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $message = 'You pull the Wings off the Fairy Ring. Now it\'s just a regular Gold Ring.';

        if($squirrel3->rngNextInt(1, 70) === 1)
            $message .= $squirrel3->rngNextFromArray([ ' (I hope you\'re happy.)', ' (See what thy hand hath wrought!)', ' (All according to plan...)' ]);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);

    }
}
