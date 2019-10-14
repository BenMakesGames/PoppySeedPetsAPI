<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class ScrollController extends PsyPetsItemController
{
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

        $em->remove($inventory);

        if(mt_rand(1, 6) === 1)
        {
            $userStatsRepository->incrementStat($user, 'Misread a Scroll');

            $pectin = \mt_rand(\mt_rand(3, 5), \mt_rand(6, 10));
            $location = $inventory->getLocation();

            for($i = 0; $i < $pectin; $i++)
                $inventoryService->receiveItem('Pectin', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

            $em->flush();

            return $responseService->itemActionSuccess('You begin to read the scroll, but mispronounce a line! Thick strands of Pectin stream out of the scroll, covering the floor, walls, and ceiling. In the end, you\'re able to recover ' . $pectin . ' batches of the stuff.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
        else
        {
            $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

            $possibleItems = [
                'Fruits & Veggies Box', 'Pamplemousse', 'Blackberries', 'Naner', 'Blueberries',
                'Red', 'Orange', 'Apricot', 'Melowatern', 'Honeydont', 'Tomato', 'Spicy Peps'
            ];

            $items = \mt_rand(5, mt_rand(6, mt_rand(7, 15)));

            $newInventory = [];
            $location = $inventory->getLocation();

            for($i = 0; $i < $items; $i++)
                $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleItems), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

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

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $commonItems = [
            'Flute', 'Fiberglass Flute', 'Music Note'
        ];

        $rareItems = [
            'Bass Guitar', 'Maraca', 'Melodica'
        ];

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Music Note', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location),
            $inventoryService->receiveItem(ArrayFunctions::pick_one($commonItems), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location),
            $inventoryService->receiveItem(ArrayFunctions::pick_one($rareItems), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location),
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

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        if($user->getUnlockedGreenhouse())
        {
            $expandedGreenhouseWithFarmerScroll = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Farmer Scroll', false);

            if(!$expandedGreenhouseWithFarmerScroll->getValue())
            {
                $expandedGreenhouseWithFarmerScroll->setValue(true);

                $user->increaseMaxPlants(1);

                $em->flush();

                return $responseService->itemActionSuccess('You read the scroll; another plot of space in your Greenhouse appears, as if by magic! In fact, thinking about it, it was _100%_ by magic!', [ 'itemDeleted' => true ]);
            }
        }

        $items = [
            'Wheat', 'Wheat', 'Wheat', 'Scythe', 'Creamy Milk', 'Egg', 'Grandparoot', 'Crooked Stick'
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

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

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $items = [
            'Fish', 'Fish', 'Seaweed', 'Seaweed', 'Seaweed', 'Silica Grounds', 'Silica Grounds', 'Silica Grounds'
        ];

        if(mt_rand(1, 5) === 1) $items[] = 'Crooked Stick';
        if(mt_rand(1, 5) === 1) $items[] = 'Mermaid Egg';
        if(mt_rand(1, 10) === 1) $items[] = 'Glass';
        if(mt_rand(1, 15) === 1) $items[] = 'Music Note';
        if(mt_rand(1, 25) === 1) $items[] = 'Little Strongbox';

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, summoning ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/minorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMinorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/minorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = \mt_rand(30, 50);

        $item = ArrayFunctions::pick_one([ 'Little Strongbox', 'Bag of Beans' ]);
        $location = $inventory->getLocation();

        $user->increaseMoneys($moneys);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/majorRiches/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMajorRichesScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'scroll/majorRiches/#/invoke');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $moneys = \mt_rand(60, 100);

        $item = ArrayFunctions::pick_one([ 'Striped Microcline', 'Firestone', 'Moon Pearl', 'Blackonite' ]);
        $location = $inventory->getLocation();

        $user->increaseMoneys($moneys);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . $moneys . '~~m~~, and ' . $item . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
