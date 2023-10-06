<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class ResourcesController extends AbstractController
{
    /**
     * @Route("/resources/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readResourcesScroll(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/resources/#/invoke');

        $numberOfItems = [
            'Tiny Scroll of Resources' => 1,
            'Scroll of Resources' => 3
        ][$inventory->getItem()->getName()];

        $possibleItems = [
            'Liquid-hot Magma',
            'Plastic', 'Crooked Stick', 'Fluff', 'Pointer',
            'Iron Ore', $squirrel3->rngNextFromArray([ 'Silver Ore', 'Silver Ore', 'Gold Ore' ]),
            'Scales', 'Yellow Dye', 'Feathers', 'Talon', 'Paper',
            'Glass', 'Gypsum'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        UserStatsRepository::incrementStat($em, $user, UserStatEnum::READ_A_SCROLL);

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

    /**
     * @Route("/resources/{inventory}/invokeFood", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readResourcesScrollForFood(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/resources/#/invokeFood');

        $numberOfItems = [
            'Tiny Scroll of Resources' => 1,
            'Scroll of Resources' => 3
        ][$inventory->getItem()->getName()];

        $possibleItems = [
            'Smallish Pumpkin', 'Tomato', 'Ginger', 'Hot Potato', 'Toad Legs', 'Spicy Peps', 'Naner', 'Sweet Beet',
            'Seaweed', 'Apricot', 'Corn', 'Mango', 'Pamplemousse', 'Carrot', 'Celery', 'Red', 'Beans', 'Wheat',
            'Rice', 'Creamy Milk', 'Orange', 'Fish', 'Onion', 'Chanterelle', 'Pineapple', 'Ponzu'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        UserStatsRepository::incrementStat($em, $user, UserStatEnum::READ_A_SCROLL);

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
