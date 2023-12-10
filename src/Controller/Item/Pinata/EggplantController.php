<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/eggplant")]
class EggplantController extends AbstractController
{
    #[Route("/{inventory}/clean", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'eggplant/#/clean');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $r = $squirrel3->rngNextInt(1, 6);
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

            $newItem = $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);
            $newItem->setSpice($inventory->getSpice());

            $message = 'You clean the Eggplant as carefully as you can, and harvest two Eggs. You also manage to extract a good amount of purplish dye from the thing! (Neat!)';
        }
        else //if($r === 6)
        {
            $eggs = 3;
            $message = 'You clean the Eggplant as carefully as you can, and successfully harvest three Eggs!';
        }

        if($eggs > 0)
        {
            for($i = 0; $i < $eggs; $i++)
            {
                $newItem = $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

                $newItem->setSpice($inventory->getSpice());
            }

            $userStatsRepository->incrementStat($user, UserStatEnum::EGGS_HARVESTED_FROM_EGGPLANTS, $eggs);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::ROTTEN_EGGPLANTS, 1);
        }

        if($squirrel3->rngNextInt(1, 100) === 1)
        {
            $inventoryService->receiveItem('Eggplant Bow', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            if($eggs === 0)
                $message .= ' Oh, but what\'s this? There\'s a purple bow inside! You clean it off, and keep it!';
            else
                $message .= ' Oh, and what\'s this? There\'s a purple bow inside! You clean it off, and keep it, as well!';
        }
        else if($squirrel3->rngNextInt(1, 100) === 1)
        {
            $inventoryService->receiveItem('Mysterious Seed', $user, $user, $user->getName() . ' got this by cleaning an Eggplant.', $location);

            if($eggs === 0)
                $message .= ' Oh, but what\'s this? There\'s a weird seed inside! You clean it off, and keep it!';
            else
                $message .= ' Oh, and what\'s this? There\'s a weird seed inside! You clean it off, and keep it, as well!';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
