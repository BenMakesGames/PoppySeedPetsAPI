<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/basket")]
class BasketController extends AbstractController
{
    #[Route("/fish/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBasketOfFish(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'basket/fish/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $spice = $inventory->getSpice();

        $loot = [
            'Fish',
            'Fish',
            $squirrel3->rngNextFromArray([ 'Fish', 'Seaweed', 'Algae', 'Sand Dollar' ]),
            $squirrel3->rngNextFromArray([ 'Silica Grounds', 'Seaweed' ]),
        ];

        if($squirrel3->rngNextInt(1, 10) === 1)
        {
            $loot[] = 'Secret Seashell';
            $exclaim = '! (Ohh!)';
        }
        else
            $exclaim = '.';

        foreach($loot as $item)
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Basket of Fish.', $location, $lockedToOwner)
                ->setSpice($spice)
            ;
        }

        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Basket of Fish; this is what was left.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You emptied the Basket of Fish, receiving ' . ArrayFunctions::list_nice($loot) . $exclaim . ' (And you keep the Fabric Mâché Basket as well, of course.)', [ 'itemDeleted' => true ]);
    }

    #[Route("/fruit/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openFruitBasket(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'basket/fruit/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $spice = $inventory->getSpice();

        $fruit = [ 'Apricot Preserves', 'Blueberries', 'Naner' ];

        foreach($fruit as $item)
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Fruit Basket.', $location, $lockedToOwner)
                ->setSpice($spice)
            ;
        }

        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Fruit Basket; this is what was left.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You emptied the Fruit Basket, receiving Apricot Preserves, Blueberries, and a Naner. (You keep the Fabric Mâché Basket as well, of course.)', [ 'itemDeleted' => true ]);
    }

    #[Route("/flower/{inventory}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openFlowerBasket(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3, Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'basket/flower/#/loot');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $weirdItem = DateFunctions::isCornMoon($clock->now) ? null : $squirrel3->rngNextFromArray([ 'Wheat Flour', 'Flour Tortilla' ]);

        $possibleFlowers = [
            'Rice Flower',
            'Rice Flower',
            'Agrimony',
            'Coriander Flower',
            'Sunflower',
            'Red Clover',
            'Purple Violet',
        ];

        $items = [];
        $weird = 0;

        for($i = 0; $i < 4; $i++)
        {
            if($weirdItem && $squirrel3->rngNextInt(1, 8) === 1)
            {
                $itemName = $weirdItem;
                $weird++;
            }
            else
                $itemName = $squirrel3->rngNextFromArray($possibleFlowers);

            $items[] = $itemName;

            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in a Flower Basket.', $location, $lockedToOwner);
        }

        $inventoryService->receiveItem('Fabric Mâché Basket', $user, $user, $user->getName() . ' took everything out of a Flower Basket; this is what was left.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        $message = 'You emptied the Flower Basket, receiving ' . ArrayFunctions::list_nice($items) . '.';

        if($weird > 0)
            $message .= ' (I\'m not sure all of those are "flowers", exactly... uh, but anyway, you keep the Fabric Mâché Basket, too.)';
        else
            $message .= ' (You keep the Fabric Mâché Basket as well, of course.)';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
