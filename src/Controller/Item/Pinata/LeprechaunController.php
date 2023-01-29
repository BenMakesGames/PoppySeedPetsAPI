<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/leprechaun")
 */
class LeprechaunController extends AbstractController
{
    /**
     * @Route("/potOfGold/{inventory}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function lootPotOfGold(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'leprechaun/potOfGold/#/loot');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::LOOTED_A_POT_OF_GOLD);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Rainbow', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Empty Cauldron', $user, $user, 'The remains of a Pot of Gold that ' . $user->getName() . ' looted.', $location, $locked);

        $em->flush();

        $responseService->addFlashMessage('You find three Gold Bars! Oh: and the Rainbow! Oh: and keep the Empty Cauldron, too. Why not.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/greenScroll/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readGreenScroll(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        ResponseService $responseService, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'leprechaun/greenScroll/#/read');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);
        $userStatsRepository->incrementStat($user, 'Read ' . $inventory->getItem()->getNameWithArticle());

        $numberOfItems = 3;

        $possibleItems = [
            'Green Egg', 'Green Gummies', 'Short Glass of Greenade', 'Green Bow', 'Green Muffin'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [];

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $item = $squirrel3->rngNextFromArray($possibleItems);
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
            $listOfItems[] = $item;
        }

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
