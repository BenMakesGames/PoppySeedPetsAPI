<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/noetalaEgg")
 */
class NoetalaEggController extends AbstractController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'noetalaEgg/#/open');

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, 'This oozed out of a Noetala Egg that ' . $user->getName() . ' opened.', $location);

        // 75% chance of more Fluff
        $includeFluff = $squirrel3->rngNextInt(1, 4) != 1;

        $loot = $squirrel3->rngNextFromArray([
            'Quintessence', 'Talon', 'Tentacle', 'Green Dye'
        ]);

        if($includeFluff)
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' peeled this off a Noetala Egg.', $location);

        $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' harvested this from a Noetala Egg.', $location);

        $em->remove($inventory);

        $em->flush();

        if($includeFluff)
            return $responseService->itemActionSuccess('You pull the clinging fibers off of the Noetala Egg, and break it open; a strange, purple liquid oozes out. Your reward? ' . $loot . '. (Well, and some Fluff from the fibers.)' , [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You break the egg open; a strange, purple liquid oozes out. Your reward? ' . $loot . '.' , [ 'itemDeleted' => true ]);
    }
}
