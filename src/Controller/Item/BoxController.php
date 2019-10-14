<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\StringFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetService;
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
     * @Route("/ores/{box}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($box, 'box/ores/#/loot');

        $location = $box->getLocation();

        $possibleOres = [
            'Iron Ore', 'Iron Ore', 'Iron Ore',
            'Silver Ore', 'Silver Ore', 'Silver Ore',
            'Gold Ore', 'Gold Ore',
            'XOR',
        ];

        $stat = $userStatsRepository->incrementStat($user, 'Looted a ' . $box->getItem()->getName());

        $numberOfItems = mt_rand(3, 5);
        $containsLobster = $stat->getValue() === 1 || mt_rand(1, 4) === 1;

        if($containsLobster)
            $numberOfItems = max(3, $numberOfItems - 1);

        for($i = 0; $i < $numberOfItems; $i++)
            $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleOres), $user, $box->getCreatedBy(), 'Found inside a ' . $box->getItem()->getName() . '.', $location);

        $em->remove($box);

        $message = 'Sifting through the box, you found ' . $numberOfItems . ' good chunks of ore!';

        if($containsLobster)
        {
            /** @var Pet $pet */
            $pet = ArrayFunctions::pick_one($petRepository->findBy([ 'owner' => $user, 'inDaycare' => false ]));

            $message .= "\n\nWait, what!? One of the rocks moved!";

            if($pet === null)
                $message .= "\n\nA lobster claw reached out from underneath and pinched you before scuttling away!";
            else
            {
                $inventoryService->receiveItem('Fish', $user, $box->getCreatedBy(), 'Found inside a lobster inside a ' . $box->getItem()->getName() . '.', $location);
                $changes = new PetChanges($pet);
                $petService->gainExp($pet, 2, [ 'dexterity', 'strength', 'brawl' ]);
                $pet->increaseEsteem(3);
                $message .= "\n\nA lobster claw reached out from underneath and tried to pinch you, but " . $pet->getName() . " stepped in and beat it up!\n\nThat was a little scary, but hey: +1 Fish meat!";
                $responseService->createActivityLog($pet, 'While ' . $user->getName() . ' was sifting through a box of ore, a lobster jumped out and tried to attack them! ' . $pet->getName() . ' stepped in and saved the day! It was a little scary, but hey: +1 Fish meat!', '', $changes->compare($pet));
            }
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

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

        $location = $inventory->getLocation();

        if(mt_rand(1, 50) === 1)
        {
            $message = "What boxes will be in _this_ Box Box, I wonder?\n\nWait, what? It's _another_ Box Box?";

            $userStatsRepository->incrementStat($user, 'Found a Box Box Inside a Box Box');

            $inventoryService->receiveItem('Box Box', $user, $user, $user->getName() . ' found this in a Box Box... huh...', $location);
        }
        else
        {
            $possibleItems = [
                'Baker\'s Box',
                'Fruits & Veggies Box',
                'Handicrafts Supply Box',
                'Little Strongbox',
            ];

            if(mt_rand(1, 3) === 0)
            {
                $possibleItems[] = ArrayFunctions::pick_one([
                    'Sandbox',
                    'Jukebox',
                    'Pepperbox',
                ]);
            }

            if(mt_rand(1, 20) === 0)
            {
                $possibleItems[] = ArrayFunctions::pick_one([
                    '4th of July Box',
                    // TODO: other holiday boxes
                ]);
            }

            shuffle($possibleItems);

            $message = "What boxes will be in _this_ Box Box, I wonder?\n\nOh: " . $possibleItems[0] . " and " . $possibleItems[1] . ", apparently!";

            $inventoryService->receiveItem($possibleItems[0], $user, $user, $user->getName() . ' found this in a Box Box.', $location);
            $inventoryService->receiveItem($possibleItems[1], $user, $user, $user->getName() . ' found this in a Box Box.', $location);
        }

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
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
        {
            $itemName = ArrayFunctions::pick_one(['Limestone', 'Glass', 'Iron Bar', 'Iron Ore', 'Silver Ore']);

            if($itemName === 'Limestone')
                $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '. I don\'t know how it fit in there, either. Your guess is as good as mine.';
            else
                $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

            $newInventory[] = $inventoryService->receiveItem($itemName, $user, $user, $description, $location);
        }

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
     * @Route("/pepperbox/{inventory}/disassemble", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function disassemblePepperbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/pepperbox/#/disassemble');

        $newInventory = [];

        $peppers = \mt_rand(4, \mt_rand(6, 10));

        $description = $user->getName() . ' got this by taking apart a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $peppers; $i++)
            $newInventory[] = $inventoryService->receiveItem('Spicy Peps', $user, $user, $description, $location);

        $userStatsRepository->incrementStat($user, 'Disassembled a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take apart the Pepperbox into its constituent pieces...', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/jukebox/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function listenToJukebox(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository, PetService $petService,
        EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/jukebox/#/listen');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $listenedToJukebox = $userQuestRepository->findOrCreate($user, 'Listened to Jukebox', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $listenedToJukebox->getValue())
            return $responseService->itemActionSuccess('You already listened to the Jukebox today. Everyone knows that Jukeboxes can only be listened to once per day.');

        if($inventory->getLocation() !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('This game doesn\'t have sound, so there wouldn\'t be much sense in playing the Jukebox for yourself. Try playing it at home, where your pets can hear. (It\'s not that there\'s no sound in Poppy Seed Pets, it\'s just that it\'s not manifested in the game interface. (Is this making sense?))');

        $listenedToJukebox->setValue($today);

        $pets = $petRepository->findBy([
            'owner' => $user->getId(),
            'inDaycare' => 0
        ]);

        $petNames = [];

        foreach($pets as $pet)
        {
            $petNames[] = $pet->getName();
            $changes = new PetChanges($pet);

            $petService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
            $pet->increaseSafety(2);

            $responseService->createActivityLog($pet, $pet->getName() . ' listened to the Jukebox.', '', $changes->compare($pet));
        }

        $em->flush();

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($petNames) . ' enjoyed listening to the Jukebox!');
    }

    /**
     * @Route("/sandbox/{inventory}/raid", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function raidSandbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/sandbox/#/raid');

        $sand = \mt_rand(6, \mt_rand(7, 12));

        $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        $a = mt_rand(1, 100);
        $extraItem = null;

        if($a === 0 || $a === 1)
        {
            $extraItem = [ 'name' => 'Striped Microcline', 'description' => 'a pretty, striped rock' ];
            $sand--;
        }
        else if($a === 2)
        {
            // TODO: password
            /*$extraItem = 'Password';
            $sand--;*/
        }
        else if($a === 3)
        {
            $extraItem = [ 'name' => 'Garden Shovel', 'description' => 'a shovel' ];
            $sand -= 3;
        }
        else if($a === 4)
        {
            $extraItem = [ 'name' => 'Plastic Idol', 'description' => 'a plastic figurine of some kind' ];
            $sand--;
        }

        if($extraItem)
        {
            $inventoryService->receiveItem($extraItem['name'], $user, $user, $description, $location);
            $itemActionMessage = 'You raid the Sandbox, finding ' . $sand . ' batches, or piles, or whatever of Silica Grounds, _but also_ ' . $extraItem['description'] . '!';
        }
        else
            $itemActionMessage = 'You raid the Sandbox, finding ' . $sand . ' batches, or piles, or whatever of Silica Grounds.';

        for($i = 0; $i < $sand; $i++)
            $inventoryService->receiveItem('Silica Grounds', $user, $user, $description, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($itemActionMessage, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
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
            'World\'s Best Sugar Cookie', 'Glowing Four-sided Die'
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
            'Glowing Six-sided Die',
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

        $em->remove($key);
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

        $items = [
            'Silver Bar',
            'Gold Bar',
            'Gold Bar',
            'Glowing Six-sided Die',
        ];

        $items[] = ArrayFunctions::pick_one([
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Pepperbox'
        ]);

        $items[] = ArrayFunctions::pick_one([
            'Minor Scroll of Riches',
            'Magic Hourglass',
        ]);

        $items[] = ArrayFunctions::pick_one([
            'Scroll of Fruit',
            'Scroll of the Sea'
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($key);
        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . $moneys . '~~m~~, ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/outrageously-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOutrageouslyStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/outrageously-strongbox/#/open');

        $key = $inventoryRepository->findOneByName($user, 'Gold Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Major Scroll of Riches',
            'Weird, Blue Egg',
            'Dumbbell',
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($key);
        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}