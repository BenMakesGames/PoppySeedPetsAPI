<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
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
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $this->validateInventory($inventory, 'eggplant/#/clean');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $r = mt_rand(1, 6);
        $eggs = 0;

        if($r === 1)
        {
            $message = 'You clean the Eggplant as carefully as you can, but the insides are all horrible and rotten; in the end, nothing is recoverable! Stupid Eggplant! >:(';
        }
        else if($r === 2)
        {
            $eggs = 1;
            $message = 'You clean the Eggplant as carefully as you can, but most of it is no good, and you\'re only able to harvest one Egg! :(';
        }
        else if($r === 3 || $r === 4)
        {
            $eggs = 2;
            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. (Not too bad... right?)';
        }
        else if($r === 5)
        {
            $eggs = 2;
            $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. You also manage to extract a good amount of purplish dye from the thing! (Neat!)';
        }
        else if($r === 6)
        {
            $eggs = 3;
            $message = 'You clean the Eggplant as carefully as you can, and successfully harvest three Eggs!';
        }

        if($eggs > 0)
        {
            for($i = 0; $i < $eggs; $i++)
                $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            $userStatsRepository->incrementStat($user, UserStatEnum::EGGS_HARVESTED_FROM_EGGPLANTS, $eggs);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::ROTTEN_EGGPLANTS, 1);
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
