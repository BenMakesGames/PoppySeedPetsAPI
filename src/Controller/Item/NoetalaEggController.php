<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/noetalaEgg")
 */
class NoetalaEggController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'noetalaEgg/#/open');

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' peeled this off a Noetala Egg', $location);

        // 75% chance of more Fluff
        if(mt_rand(1, 4) != 1)
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' peeled this off a Noetala Egg', $location);

        $loot = ArrayFunctions::pick_one([
            'Quintessence', 'Baking Soda', 'Tentacle', 'Green Dye'
        ]);

        $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' harvested this from a Noetala Egg', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You pull the clinging fibers off of the Noetala Egg, and break it open. Your reward? ' . $loot . '. (Well, and some Fluff from the fibers.)' , [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}