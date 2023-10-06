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
class FruitScrollController extends AbstractController
{
    /**
     * @Route("/fruit/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeFruitScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/fruit/#/invoke');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $r = $squirrel3->rngNextInt(1, 6);

        if($r === 1)
        {
            UserStatsRepository::incrementStat($em, $user, 'Misread a Scroll');

            $pectin = $squirrel3->rngNextInt($squirrel3->rngNextInt(3, 5), $squirrel3->rngNextInt(6, 10));
            $location = $inventory->getLocation();

            for($i = 0; $i < $pectin; $i++)
                $inventoryService->receiveItem('Pectin', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $em->flush();

            $responseService->addFlashMessage('You begin to read the scroll, but mispronounce a line! Thick strands of Pectin stream out of the scroll, covering the floor, walls, and ceiling. In the end, you\'re able to recover ' . $pectin . ' batches of the stuff.');

            return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
        }
        else if($r === 2 || $r === 3) // get a bunch of the same item
        {
            UserStatsRepository::incrementStat($em, $user, UserStatEnum::READ_A_SCROLL);

            $item = $squirrel3->rngNextFromArray([
                'Pamplemousse', 'Blackberries', 'Bunch of Naners', 'Blueberries', 'Red',
                'Orange', 'Apricot', 'Melowatern', 'Honeydont', 'Pineapple',
                'Yellowy Lime', 'Ponzu',
            ]);

            $numItems = $squirrel3->rngNextInt(5, $squirrel3->rngNextInt(6, 12));
            $location = $inventory->getLocation();

            for($i = 0; $i < $numItems; $i++)
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $em->flush();

            $message = 'You read the scroll perfectly, summoning ' . $numItems . '&times; ' . $item . '!';

            if($squirrel3->rngNextInt(1, 10) == 10)
                $message .= "\n\nAs the scroll dissolves, the motes of nothingness it leaves behind form words in your mind:\n\n\"Tie a String to a Fruit Fly, and find my reward.\"\n\nFascinating! Who knew motes of nothingness could talk!";

            $responseService->addFlashMessage($message);

            return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
        }
        else // get a bunch of different items
        {
            UserStatsRepository::incrementStat($em, $user, UserStatEnum::READ_A_SCROLL);

            $possibleItems = [
                'Fruits & Veggies Box',

                'Pamplemousse', 'Blackberries', 'Naner', 'Blueberries', 'Red',
                'Orange', 'Apricot', 'Melowatern', 'Honeydont', 'Pineapple',
                'Yellowy Lime', 'Ponzu',

                // technically fruit
                'Tomato', 'Spicy Peps', 'Cucumber',
            ];

            $numItems = $squirrel3->rngNextInt(5, $squirrel3->rngNextInt(6, $squirrel3->rngNextInt(7, 15)));

            $newInventory = [];
            $location = $inventory->getLocation();

            for($i = 0; $i < $numItems; $i++)
                $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
            sort($itemList);

            $em->flush();

            $responseService->addFlashMessage('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($itemList) . '.');

            return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
        }
    }
}
