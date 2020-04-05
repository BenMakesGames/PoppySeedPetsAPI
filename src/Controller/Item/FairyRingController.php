<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fairyRing")
 */
class FairyRingController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/takeApart", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeApart(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'fairyRing/#/takeApart');

        $user = $this->getUser();

        $inventory->changeItem($itemRepository->findOneByName('Gold Ring'));

        $inventoryService->receiveItem('Wings', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled these off a Fairy Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $message = 'You pull the Wings off the Fairy Ring. Now it\'s just a regular Gold Ring.';

        if(mt_rand(1, 70) === 1)
            $message .= ArrayFunctions::pick_one([ ' (I hope you\'re happy.)', ' (See what thy hand hath wrought!)', ' (All according to plan...)' ]);

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);

    }
}
