<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class ScrollController extends PoppySeedPetsItemController
{
    /**
     * @Route("/fairy/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readFairyScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/fairy/#/read');
        $this->validateHouseSpace($inventory, $inventoryService);

        $lameItems = [ 'Toadstool', 'Charcoal', 'Toad Legs', 'Bird\'s-foot Trefoil', 'Coriander Flower' ];

        $loot = [
            'Wings',
            ArrayFunctions::pick_one($lameItems),
            ArrayFunctions::pick_one($lameItems),
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' summoned this by reading a Fairy\'s Scroll.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($loot) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/fruit/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeFruitScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/fruit/#/invoke');
        $this->validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $r = mt_rand(1, 6);

        if($r === 1)
        {
            $userStatsRepository->incrementStat($user, 'Misread a Scroll');

            $pectin = mt_rand(mt_rand(3, 5), mt_rand(6, 10));
            $location = $inventory->getLocation();

            for($i = 0; $i < $pectin; $i++)
                $inventoryService->receiveItem('Pectin', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $em->flush();

            return $responseService->itemActionSuccess('You begin to read the scroll, but mispronounce a line! Thick strands of Pectin stream out of the scroll, covering the floor, walls, and ceiling. In the end, you\'re able to recover ' . $pectin . ' batches of the stuff.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
        else if($r === 2 || $r === 3)
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

            $item = ArrayFunctions::pick_one([
                'Pamplemousse', 'Blackberries', 'Naner', 'Blueberries',
                'Red', 'Orange', 'Apricot', 'Melowatern', 'Honeydont', 'Tomato', 'Spicy Peps'
            ]);

            $numItems = mt_rand(5, mt_rand(6, 12));
            $location = $inventory->getLocation();

            for($i = 0; $i < $numItems; $i++)
                $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $em->flush();

            return $responseService->itemActionSuccess('You read the scroll perfectly, summoning ' . $numItems . '&times; ' . $item . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

            $possibleItems = [
                'Fruits & Veggies Box', 'Pamplemousse', 'Blackberries', 'Naner', 'Blueberries',
                'Red', 'Orange', 'Apricot', 'Melowatern', 'Honeydont', 'Tomato', 'Spicy Peps'
            ];

            $numItems = mt_rand(5, mt_rand(6, mt_rand(7, 15)));

            $newInventory = [];
            $location = $inventory->getLocation();

            for($i = 0; $i < $numItems; $i++)
                $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

            $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
            sort($itemList);

            $em->flush();

            return $responseService->itemActionSuccess('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
    }
    /**
     * @Route("/music/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMusicScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/music/#/invoke');
        $this->validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $commonItems = [
            'Flute', 'Fiberglass Flute', 'Music Note', 'Gold Triangle'
        ];

        $rareItems = [
            'Bass Guitar', 'Maraca', 'Melodica'
        ];

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Music Note', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem(ArrayFunctions::pick_one($commonItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem(ArrayFunctions::pick_one($rareItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
        ];

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/farmers/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeFarmerScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/farmers/#/invoke');
        $this->validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        if($user->getGreenhouse())
        {
            $expandedGreenhouseWithFarmerScroll = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Farmer Scroll', false);

            if(!$expandedGreenhouseWithFarmerScroll->getValue())
            {
                $expandedGreenhouseWithFarmerScroll->setValue(true);

                $user->getGreenhouse()->increaseMaxPlants(1);

                $em->flush();

                return $responseService->itemActionSuccess('You read the scroll; another plot of space in your Greenhouse appears, as if by magic! In fact, thinking about it, it was _100%_ by magic!', [ 'itemDeleted' => true ]);
            }
        }

        $items = [
            'Straw Hat', 'Wheat', 'Scythe', 'Creamy Milk', 'Egg', 'Grandparoot', 'Crooked Stick', 'Potato'
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, summoning ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/sea/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeSeaScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/sea/#/invoke');
        $this->validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $items = [
            'Fish',
            'Seaweed',
            'Silica Grounds',
            ArrayFunctions::pick_one([ 'Fish', 'Tentacle' ]),
            ArrayFunctions::pick_one([ 'Seaweed', 'Fish' ]),
            ArrayFunctions::pick_one([ 'Seaweed', 'Silica Grounds' ]),
            ArrayFunctions::pick_one([ 'Seaweed', 'Crooked Stick' ]),
        ];

        if(mt_rand(1, 4) === 1) $items[] = 'Glass';
        if(mt_rand(1, 5) === 1) $items[] = 'Music Note';
        if(mt_rand(1, 8) === 1) $items[] = 'Mermaid Egg';
        if(mt_rand(1, 10) === 1) $items[] = 'Secret Seashell';
        if(mt_rand(1, 15) === 1) $items[] = 'Iron Ore';
        if(mt_rand(1, 20) === 1) $items[] = 'Little Strongbox';

        $newInventory = [];
        $location = $inventory->getLocation();

        sort($items);

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, summoning ' . ArrayFunctions::list_nice($items) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/minorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMinorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/minorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = mt_rand(30, 50);

        $item = ArrayFunctions::pick_one([ 'Little Strongbox', 'Bag of Beans' ]);
        $location = $inventory->getLocation();

        if(mt_rand(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Minor Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Minor Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/majorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMajorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/majorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = mt_rand(60, 100);

        $item = ArrayFunctions::pick_one([ 'Striped Microcline', 'Firestone', 'Moon Pearl', 'Blackonite' ]);
        $location = $inventory->getLocation();

        if(mt_rand(1, 10) === 1)
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches. (Hopefully not out of a bank, or dragon\'s hoard, or something...)');
        else
            $transactionService->getMoney($user, $moneys, 'Conjured by a Scroll of Major Riches.');

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/resources/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readResourcesScroll(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/resources/#/invoke');

        $numberOfItems = [
            'Tiny Scroll of Resources' => 1,
            'Scroll of Resources' => 3
        ][$inventory->getItem()->getName()];

        $possibleItems = [
            'Liquid-hot Magma',
            'Plastic', 'Crooked Stick', 'Fluff', 'Pointer',
            'Iron Ore', ArrayFunctions::pick_one([ 'Silver Ore', 'Silver Ore', 'Gold Ore' ]),
            'Scales', 'Yellow Dye', 'Feathers', 'Talon', 'Paper',
            'Glass', 'Gypsum'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [];

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $item = ArrayFunctions::pick_one($possibleItems);
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
            $listOfItems[] = $item;
        }

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
