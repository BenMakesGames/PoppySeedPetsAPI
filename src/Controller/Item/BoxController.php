<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/box")
 */
class BoxController extends PsyPetsItemController
{
    /**
     * @Route("/box/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBoxBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/box/#/open');

        $itemName = ArrayFunctions::pick_one([
            'Baker\'s Box',
            'Fruits & Veggies Box',
            'Handicrafts Supply Box',
            'Little Strongbox',
        ]);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("What kind of box will be in _this_ Box Box, I wonder?\n\nOh: the " . $itemName . " kind, apparently!", [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
    /**
     * @Route("/bakers/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBakers(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bakers/#/open');

        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < 5; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Egg', 'Wheat Flour', 'Sugar', 'Creamy Milk' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location);

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Corn Syrup', 'Baker\'s Yeast', 'Cocoa Beans', 'Baking Soda', 'Cream of Tartar' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/fruits-n-veggies/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fruits-n-veggies/#/open');

        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < 5; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Orange', 'Red', 'Blackberries', 'Blueberries']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/handicrafts/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openHandicrafts(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/handicrafts/#/open');

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location)
        ];

        for($i = 0; $i < 5; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Fluff', 'Plastic', 'Green Dye', 'Yellow Dye', 'Paper', 'Glue']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Limestone', 'Glass', 'Iron Bar', 'Iron Ore', 'Silver Ore']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/bagOfBeans/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBagOfBeans(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bagOfBeans/#/open');

        $newInventory = [];

        $beans = \mt_rand(6, \mt_rand(7, 12));

        $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $beans; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Coffee Beans', 'Cocoa Beans', 'Beans' ]), $user, $user, $description, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You upturn the bag, finding ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/paperBag/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openPaperBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/paperBag/#/open');

        $item = ArrayFunctions::pick_one([
            'Apricot', 'Baker\'s Yeast', 'Baking Soda', 'Beans', 'Blackberry Lassi', 'Blueberries', 'Butter', 'Celery',
            'Cockroach', 'Corn', 'Cream of Tartar', 'Creamy Milk', 'Dark Matter', 'Egg', 'Fish', 'Fluff',
            'Grandparoot', 'Honeydont', 'Hot Dog', 'Iron Ore', 'Kombucha', 'Melon Bun', 'Naner', 'Oil', 'Onion',
            'Orange', 'Pamplemousse', 'Plain Yogurt', 'Quintessence', 'Red', 'Rice', 'Seaweed', 'Secret Seashell',
            'Silica Grounds', 'Smallish Pumpkin', 'Sugar', 'Toad Legs', 'Tomato', 'Wheat Flour',
            'World\'s Best Sugar Cookie',
        ]);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $inventory->getLocation());

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the bag... ah! ' . $item . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/july4/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open4thOfJulyBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/july4/#/open');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment, $location),
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment, $location),
            $inventoryService->receiveItem('Sunscreen', $user, $user, $comment, $location),
            $inventoryService->receiveItem('Red Firework', $user, $user, $comment, $location),
            $inventoryService->receiveItem('White Firework', $user, $user, $comment, $location),
            $inventoryService->receiveItem('Blue Firework', $user, $user, $comment, $location),
        ];

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/little-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openLittleStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/little-strongbox/#/open');

        $key = $inventoryRepository->findOneByName($user, 'Iron Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $moneys = mt_rand(10, mt_rand(20, mt_rand(50, mt_rand(100, 200)))); // averages 35?

        $user->increaseMoneys($moneys);

        $possibleItems = [
            'Silver Bar', 'Silver Bar',
            'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Blackberry Wine',
            'Fluff',
        ];

        $numItems = mt_rand(2, 4);
        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < $numItems; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleItems), $user, $user, $comment, $location);

        if(mt_rand(1, 4) > 1)
            $newInventory[] = $inventoryService->receiveItem('Piece of Cetgueli\'s Map', $user, $user, $comment, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . $moneys . '~~m~~, ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/very-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/very-strongbox/#/open');

        $key = $inventoryRepository->findOneByName($user, 'Silver Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $moneys = mt_rand(15, mt_rand(45, mt_rand(100, mt_rand(200, 300)))); // averages 50?

        $user->increaseMoneys($moneys);

        $possibleItems = [
            'Silver Bar',
            'Gold Bar', 'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Minor Scroll of Riches',
            'Magic Hourglass',
        ];

        $numItems = mt_rand(2, 4);
        $newInventory = [];
        $location = $inventory->getLocation();

        for($i = 0; $i < $numItems; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleItems), $user, $user, $comment, $location);

        $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([
            'Scroll of Fruit', 'Scroll of the Sea', 'Minor Scroll of Riches'
        ]), $user, $user, $comment, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . $moneys . '~~m~~, ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}