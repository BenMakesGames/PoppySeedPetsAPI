<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fruitBasket")
 */
class FruitBasketController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'fruitBasket/#/open');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Apricot Preserves', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location);
        $inventoryService->receiveItem('Blueberries', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location);
        $inventoryService->receiveItem('Naner', $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location);
        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Fruit Basket; this is what was left.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You emptied the Fruit Basket, receiving Apricot Preserves, Bluberries, and a Nanner. (You keep the Fabric Mâché Basket as well, of course.)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}