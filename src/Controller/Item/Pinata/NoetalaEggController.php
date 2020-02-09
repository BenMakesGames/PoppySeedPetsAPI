<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
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

        $inventoryService->receiveItem('Quinacridone Magenta', $user, $user, $user->getName() . ' peeled this off a Noetala Egg', $location);

        // 75% chance of more Fluff
        $includeFluff = mt_rand(1, 4) != 1;

        $loot = ArrayFunctions::pick_one([
            'Quintessence', 'Talon', 'Tentacle', 'Green Dye'
        ]);

        if($includeFluff)
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' peeled this off a Noetala Egg.', $location);

        $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' harvested this from a Noetala Egg.', $location);

        $em->remove($inventory);

        $em->flush();

        if($includeFluff)
            return $responseService->itemActionSuccess('You pull the clinging fibers off of the Noetala Egg, and break it open; a strange, purple liquid oozes out. Your reward? ' . $loot . '. (Well, and some Fluff from the fibers.)' , [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You break the egg open; a strange, purple liquid oozes out. Your reward? ' . $loot . '. (Well, and some Fluff from the fibers.)' , [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
