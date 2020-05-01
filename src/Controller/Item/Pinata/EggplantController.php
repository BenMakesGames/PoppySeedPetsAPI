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
 * @Route("/item/eggplant")
 */
class EggplantController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/clean", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'eggplant/#/clean');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $r = mt_rand(1, 6);

        if($r === 1)
        {
            $message = 'You clean the Eggplant as carefully as you can, but the insides are all horrible and rotten; in the end, nothing is recoverable! Stupid Eggplant! >:(';
        }
        else if($r === 2)
        {
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $message = 'You clean the Eggplant as carefully as you can, but most of it is no good, and you\'re only able to harvest one Egg! :(';
        }
        else if($r === 3 || $r === 4)
        {
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. (Not too bad... right?)';
        }
        else if($r === 5)
        {
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. You also manage to extract a good amount of purplish dye from the thing! (Neat!)';

        }
        else if($r === 6)
        {
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $message = 'You clean the Eggplant as carefully as you can, and successfully harvest three Eggs!';
        }

        if(mt_rand(1, 100) === 1)
        {
            $inventoryService->receiveItem('Mysterious Seed', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            if($r <= 2)
                $message .= ' Oh, but what\'s this? There\'s some kind of super-weird seed! You clean it off, and keep it!';
            else
                $message .= ' Oh, and what\'s this? There\'s some kind of super-weird seed! You clean it off, and keep it, as well!';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
