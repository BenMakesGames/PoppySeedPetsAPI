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
 * @Route("/item/basket")
 */
class BasketController extends PoppySeedPetsItemController
{
    /**
     * @Route("/fruit/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitBasket(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'basket/fruit/#/open');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Apricot Preserves', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Blueberries', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Naner', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Fruit Basket; this is what was left.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You emptied the Fruit Basket, receiving Apricot Preserves, Bluberries, and a Nanner. (You keep the Fabric Mâché Basket as well, of course.)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/flower/{inventory}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFlowerBasket(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'basket/flower/#/loot');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $possibleFlowers = [
            'Rice Flower',
            'Rice Flower',
            'Agrimony',
            'Coriander Flower',
            'Sunflower',
            'Red Clover',
            'Purple Violet',
            'Wheat Flour'
        ];

        $items = [];

        for($i = 0; $i < 4; $i++)
        {
            $itemName = ArrayFunctions::pick_one($possibleFlowers);
            $items[] = $itemName;

            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in a Flower Basket.', $location, $lockedToOwner);
        }

        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Flower Basket; this is what was left.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You emptied the Flower Basket, receiving ' . ArrayFunctions::list_nice($items) . '. (You keep the Fabric Mâché Basket as well, of course.)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}