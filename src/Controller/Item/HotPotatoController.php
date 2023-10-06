<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\UserStatsHelpers;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/hotPotato")
 */
class HotPotatoController extends AbstractController
{
    /**
     * @Route("/{inventory}/toss", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, IRandom $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $numberOfTosses = HotPotatoService::countTosses($inventory);

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/toss');

        if($squirrel3->rngNextInt(1, 10 + $numberOfTosses) <= $numberOfTosses + 1)
        {
            $spice = $inventory->getSpice();

            $allItemNames = [
                'Smashed Potatoes',
                'Liquid-hot Magma',
            ];

            for($i = 0; $i < $numberOfTosses; $i++)
            {
                $allItemNames[] = $squirrel3->rngNextFromArray([
                    'Smashed Potatoes',
                    'Liquid-hot Magma',
                    'Butter',
                    'Oil',
                    'Sour Cream',
                    'Cheese',
                    'Vinegar',
                    'Onion',
                    'Beans',
                ]);
            }

            sort($allItemNames);

            foreach($allItemNames as $itemName)
            {
                $inventoryService->receiveItem($itemName, $user, $inventory->getCreatedBy(), 'This exploded out of a Hot Potato.', $inventory->getLocation())
                    ->setSpice($spice);
                ;
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Hot Potato, but it explodes in your hands! It\'s a bit hot, but hey: you got ' . ArrayFunctions::list_nice($allItemNames) . '!', [ 'itemDeleted' => true ]);
        }
        else
        {
            UserStatsHelpers::incrementStat($em, $user, UserStatEnum::TOSSED_A_HOT_POTATO);

            return $hotPotatoService->tossItem($inventory);
        }
    }

    /**
     * @Route("/{inventory}/tossChocolateBomb", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tossChocolateBomb(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, IRandom $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/tossChocolateBomb');

        $numberOfTosses = HotPotatoService::countTosses($inventory);

        if($squirrel3->rngNextInt(1, 100) <= 10 + $numberOfTosses * 10)
        {
            $numberOfItems = 5 + $numberOfTosses;
            $spice = $inventory->getSpice();

            $loot = $squirrel3->rngNextSubsetFromArray([
                'Chocolate Bar',
                'Chocolate Bomb',
                'Chocolate Cake Pops',
                'Chocolate Chip Meringue',
                'Chocolate Chip Muffin',
                'Chocolate Ice Cream',
                'Chocolate Key',
                'Chocolate Meringue',
                'Chocolate Syrup',
                'Chocolate Toffee Matzah',
                'Chocolate-covered Honeycomb',
                'Chocolate-covered Naner',
                'Chocolate-frosted Donut',
                'Mini Chocolate Chip Cookies',
                'Orange Chocolate Bar',
                'Slice of Chocolate Cream Pie',
                'Spicy Chocolate Bar'
            ], $numberOfItems);

            foreach($loot as $itemName)
            {
                $inventoryService->receiveItem($itemName, $user, $inventory->getCreatedBy(), 'This exploded out of a Chocolate Bomb.', $inventory->getLocation(), $itemName === 'Chocolate Bomb')
                    ->setSpice($spice);
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Chocolate Bomb, but it explodes in your hands; ' . $numberOfItems . ' chocolately items fly out!', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }

    /**
     * @Route("/{inventory}/tossHongbao", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tossHongbao(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        IRandom $squirrel3, HotPotatoService $hotPotatoService, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPotato/#/tossHongbao');

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $money = $squirrel3->rngNextInt(10, 20);

            $transactionService->getMoney($user, $money, "Found this inside {$inventory->getItem()->getNameWithArticle()}.");

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess("You try to open the {$inventory->getItem()->getName()}, and succeed! (Just like a real envelope _should_ work!) There's {$money}~~m~~ inside, which you pocket.", [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory, "You try to open the {$inventory->getItem()->getName()}, but, mysteriously, it refuses. Eventually you give up, and toss it");
        }
    }
}
