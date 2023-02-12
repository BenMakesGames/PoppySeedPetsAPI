<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class FairyController extends AbstractController
{
    /**
     * @Route("/fairy/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readFairyScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'scroll/fairy/#/read');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $lameItems = [ 'Toadstool', 'Charcoal', 'Toad Legs', 'Bird\'s-foot Trefoil', 'Coriander Flower' ];

        $loot = [
            'Wings',
            $squirrel3->rngNextFromArray($lameItems),
            $squirrel3->rngNextFromArray($lameItems),
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' summoned this by reading a Fairy\'s Scroll.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($loot) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
