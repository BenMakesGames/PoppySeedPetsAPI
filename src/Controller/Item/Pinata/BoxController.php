<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\ToolBonusService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/box")
 */
class BoxController extends PoppySeedPetsItemController
{
    /**
     * @Route("/hat/{box}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openHatBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, ItemRepository $itemRepository,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($box, 'box/hat/#/open');

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $hatItem = $itemRepository->findOneByName(ArrayFunctions::pick_one([
            'Masquerade Mask',
            'Wings',
            'Merchant\'s Cap',
            'Wizarding Hat',
            'Gray Bow',
            'Cool Sunglasses',
            'Sombrero'
        ]));

        $stat = $userStatsRepository->incrementStat($user, 'Opened a ' . $box->getItem()->getName());

        if($hatItem->getName() === 'Gray Bow')
        {
            $itemComment = 'Made out of the strap of ' . $box->getItem()->getNameWithArticle() . '.';
            $message = "You open the hat box... ta-da! It\'s... EMPTY?!?!\n\nRefusing to be outdone by a box, you tie the Hat Box\'s strap into a bow.";
        }
        else if($hatItem->getName() === 'Cool Sunglasses')
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s... ' . $hatItem->getNameWithArticle() . '? (Is that a hat?)';
        }
        else if($hatItem->getName() === 'Wings')
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s... two ' . $hatItem->getName() . '! (Which are each already two wings, so it\'s kinda\' like getting four, I guess?)';

            $inventoryService->receiveItem($hatItem, $user, $box->getCreatedBy(), $itemComment, $location, $lockedToOwner);
        }
        else
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s ' . $hatItem->getNameWithArticle() . '!';
        }

        $inventoryService->receiveItem($hatItem, $user, $box->getCreatedBy(), $itemComment, $location, $lockedToOwner);

        $em->remove($box);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/ores/{box}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, PetRepository $petRepository,
        PetExperienceService $petExperienceService, UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($box, 'box/ores/#/loot');
        $this->validateHouseSpace($box, $inventoryService);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

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
            $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleOres), $user, $box->getCreatedBy(), 'Found inside a ' . $box->getItem()->getName() . '.', $location, $lockedToOwner);

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
                $inventoryService->receiveItem('Fish', $user, $box->getCreatedBy(), 'Found inside a lobster inside a ' . $box->getItem()->getName() . '.', $location, $lockedToOwner);
                $changes = new PetChanges($pet);
                $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
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
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        if(mt_rand(1, 50) === 1)
        {
            $message = "What boxes will be in _this_ Box Box, I wonder?\n\nWait, what? It's _another_ Box Box?";

            $userStatsRepository->incrementStat($user, 'Found a Box Box Inside a Box Box');

            $inventoryService->receiveItem('Box Box', $user, $user, $user->getName() . ' found this in a Box Box... huh...', $location, $inventory->getLockedToOwner());
        }
        else
        {
            $possibleItems = [
                'Baker\'s Box',
                'Fruits & Veggies Box',
                'Handicrafts Supply Box',
                'Little Strongbox',
                'Sandbox',
                'Jukebox',
                'Pepperbox',
            ];

            if(mt_rand(1, 4) === 0)
                $possibleItems[] = [ ArrayFunctions::pick_one([ 'Cereal Box', 'Hat Box' ]) ];

            if(mt_rand(1, 20) === 0)
            {
                $possibleItems[] = ArrayFunctions::pick_one([
                    '4th of July Box',
                    'New Year Box',
                    // TODO: other holiday boxes
                ]);
            }

            shuffle($possibleItems);

            $message = "What boxes will be in _this_ Box Box, I wonder?\n\nOh: " . $possibleItems[0] . " and " . $possibleItems[1] . ", apparently!";

            $inventoryService->receiveItem($possibleItems[0], $user, $user, $user->getName() . ' found this in a Box Box.', $location, $inventory->getLockedToOwner());
            $inventoryService->receiveItem($possibleItems[1], $user, $user, $user->getName() . ' found this in a Box Box.', $location, $inventory->getLockedToOwner());
        }

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/cereal/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openCerealBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/cereal/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();

        $message = $user->getName() . ' got this from a Cereal Box.';

        $newInventory[] = $inventoryService->receiveItem('Corn', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Wheat', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Rice', $user, $user, $message, $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 7; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Corn', 'Wheat', 'Rice' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/bakers/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBakers(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository,
        ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bakers/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();

        $freeBasicRecipes = $userQuestRepository->findOrCreate($user, 'Got free Basic Recipes', false);
        if(!$freeBasicRecipes->getValue())
        {
            $newInventory[] = $inventoryService->receiveItem('Cooking 101', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());
            $freeBasicRecipes->setValue(true);
        }

        $newInventory[] = $inventoryService->receiveItem('Wheat', $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Egg', 'Wheat Flour', 'Sugar', 'Creamy Milk' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Corn Syrup', 'Yeast', 'Cocoa Beans', 'Baking Soda', 'Cream of Tartar' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 4) === 1)
            $newInventory[] = $inventoryService->receiveItem('Cobbler Recipe', $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/fruits-n-veggies/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository,
        ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fruits-n-veggies/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();

        $freeBasicRecipes = $userQuestRepository->findOrCreate($user, 'Got free Basic Recipes', false);
        if(!$freeBasicRecipes->getValue())
        {
            $newInventory[] = $inventoryService->receiveItem('Cooking 101', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());
            $freeBasicRecipes->setValue(true);
        }

        // guaranteed orange or red
        $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Orange', 'Red' ]), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Orange', 'Red', 'Blackberries', 'Blueberries' ]), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet' ]), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/handicrafts/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openHandicrafts(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/handicrafts/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner()),
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner())
        ];

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Fluff', 'Plastic', 'Green Dye', 'Yellow Dye', 'Paper', 'Glue']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 3; $i++)
        {
            $itemName = ArrayFunctions::pick_one([ 'Limestone', 'Glass', 'Iron Bar', 'Iron Ore', 'Silver Ore' ]);

            if($itemName === 'Limestone')
                $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '. I don\'t know how it fit in there, either. Your guess is as good as mine.';
            else
                $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

            $newInventory[] = $inventoryService->receiveItem($itemName, $user, $user, $description, $location, $inventory->getLockedToOwner());
        }

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/gaming/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openGamingBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/gaming/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $dice = [
            'Glowing Four-sided Die',
            'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', // most-common
            'Glowing Eight-sided Die'
        ];

        // two dice
        for($i = 0; $i < 2; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($dice), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        $itemName = ArrayFunctions::pick_one([
            'Browser Cookie', 'Browser Cookie',
            'Sweet Coffee Bean Tea', 'Coffee Bean Tea', 'Sweet Black Tea', // caffeine
            'Music Note',
            'Toadstool'
        ]);

        // one of something else
        $newInventory[] = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem('Glowing Twenty-sided Die', $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @param Inventory[] $newInventory
     */
    private function countRemoveFlushAndRespond(
        string $messagePrefix,
        UserStatsRepository $userStatsRepository, User $user, Inventory $inventory, array $newInventory,
        ResponseService $responseService, EntityManagerInterface $em, ToolBonusService $toolBonusService
    ): JsonResponse
    {
        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) use($toolBonusService) { return $toolBonusService->getNameWithBonus($i); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        $responseService->addReloadInventory();

        return $responseService->itemActionSuccess($messagePrefix . ' ' . ArrayFunctions::list_nice($itemList) . '.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/bagOfBeans/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBagOfBeans(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bagOfBeans/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $beans = mt_rand(6, mt_rand(7, 12));

        $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $beans; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Coffee Beans', 'Cocoa Beans', 'Beans' ]), $user, $user, $description, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('You upturn the bag, finding', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
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
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $peppers = mt_rand(4, mt_rand(6, 10));

        $description = $user->getName() . ' got this by taking apart a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $peppers; $i++)
            $newInventory[] = $inventoryService->receiveItem('Spicy Peps', $user, $user, $description, $location, $inventory->getLockedToOwner());

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
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository, PetExperienceService $petExperienceService,
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

            $petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
            $pet->increaseSafety(2);

            $responseService->createActivityLog($pet, $pet->getName() . ' listened to the Jukebox.', '', $changes->compare($pet))
                ->setViewed()
            ;
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
        $this->validateHouseSpace($inventory, $inventoryService);

        $sand = mt_rand(6, mt_rand(7, 12));

        $description = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';
        $location = $inventory->getLocation();

        $a = mt_rand(1, 100);
        $extraItem = null;

        if($a === 1 || $a === 2)
        {
            $extraItem = [ 'name' => 'Striped Microcline', 'description' => 'a pretty, striped rock' ];
            $sand--;
        }
        else if($a >= 3 && $a <= 6)
        {
            $extraItem = [ 'name' => 'Password', 'description' => 'someone\'s password' ];
            $sand--;
        }
        else if($a === 7 || $a === 8)
        {
            $extraItem = [ 'name' => 'Garden Shovel', 'description' => 'a shovel' ];
            $sand -= 3;
        }
        else if($a >= 9 && $a <= 11)
        {
            $extraItem = [ 'name' => 'Plastic Idol', 'description' => 'a plastic figurine of some kind' ];
            $sand--;
        }
        else if($a === 12)
        {
            $extraItem = [ 'name' => 'Species Transmigration Serum', 'description' => 'a syringe!? Goodness! That\'s exceptionally unsafe' ];
            $sand--;
        }
        else if($a === 13 || $a === 14)
        {
            $extraItem = [ 'name' => 'Secret Seashell', 'description' => 'a Secret Seashell! Ooh' ];
        }

        if($extraItem)
        {
            $inventoryService->receiveItem($extraItem['name'], $user, $user, $description, $location, $inventory->getLockedToOwner());
            $itemActionMessage = 'You raid the Sandbox, finding ' . $sand . ' batches, or piles, or whatever of Silica Grounds, _but also_ ' . $extraItem['description'] . '!';
        }
        else
            $itemActionMessage = 'You raid the Sandbox, finding ' . $sand . ' batches, or piles, or whatever of Silica Grounds.';

        for($i = 0; $i < $sand; $i++)
            $inventoryService->receiveItem('Silica Grounds', $user, $user, $description, $location, $inventory->getLockedToOwner());

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
            'Apricot', 'Yeast', 'Baking Soda', 'Beans', 'Blackberry Lassi', 'Blueberries', 'Butter', 'Celery',
            'Cockroach', 'Corn', 'Cream of Tartar', 'Creamy Milk', 'Dark Matter', 'Egg', 'Fish', 'Fluff',
            'Grandparoot', 'Honeydont', 'Hot Dog', 'Iron Ore', 'Kombucha', 'Melon Bun', 'Naner', 'Oil', 'Onion',
            'Orange', 'Pamplemousse', 'Plain Yogurt', 'Quintessence', 'Red', 'Rice', 'Seaweed', 'Secret Seashell',
            'Silica Grounds', 'Smallish Pumpkin', 'Sugar', 'Toad Legs', 'Tomato', 'Wheat Flour',
            'World\'s Best Sugar Cookie', 'Glowing Four-sided Die', 'Mint', 'Mixed Nuts', 'Canned Food',
        ]);

        if($item === 'Cockroach' && mt_rand(1, 3) === 1)
        {
            $numRoaches = mt_rand(6, 8);

            for($i = 0; $i < $numRoaches; $i++)
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());

            $message = 'You open the bag... agh! It\'s swarming with roaches!!';
        }
        else
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());
            $message = 'You open the bag... ah! ' . $item . '!';
        }

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        $em->flush();

        $responseService->addReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/july4/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open4thOfJulyBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/july4/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $newInventory = [
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Sunscreen', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Red Firework', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('White Firework', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Blue Firework', $user, $user, $comment, $location, $lockedToOwner),
        ];

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/newYear/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openNewYearBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/newYear/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $newInventory = [
            $inventoryService->receiveItem('White Firework', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Silver Bar', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('White Cloth', $user, $user, $comment, $location, $lockedToOwner),
        ];

        $alcohol = [
            'Blackberry Wine',
            'Blueberry Wine',
            'Red Wine',
            'Eggnog',
        ];

        for($x = mt_rand(4, 5); $x > 0; $x--)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($alcohol), $user, $user, $comment, $location, $lockedToOwner);

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/little-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openLittleStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/little-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Iron Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $moneys = mt_rand(10, mt_rand(20, mt_rand(50, mt_rand(100, 200)))); // averages 35?

        $transactionService->getMoney($user, $moneys, 'Found inside a ' . $inventory->getItem()->getName() . '.');

        $possibleItems = [
            'Silver Bar', 'Silver Bar',
            'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Blackberry Wine',
            'Fluff',
            'Glowing Six-sided Die',
        ];

        $numItems = mt_rand(2, mt_rand(3, 4));
        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < $numItems; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($possibleItems), $user, $user, $comment, $location);

        $newInventory[] = $inventoryService->receiveItem('Piece of Cetgueli\'s Map', $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return $this->countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/very-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/very-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Silver Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $moneys = mt_rand(15, mt_rand(45, mt_rand(100, mt_rand(200, 300)))); // averages 50?

        $transactionService->getMoney($user, $moneys, 'Found inside a ' . $inventory->getItem()->getName() . '.');

        $items = [
            'Silver Bar',
            'Gold Bar',
            'Gold Bar',
            'Glowing Six-sided Die',
        ];

        $items[] = ArrayFunctions::pick_one([
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Pepperbox',
        ]);

        $items[] = ArrayFunctions::pick_one([
            'Minor Scroll of Riches',
            'Magic Hourglass',
        ]);

        $items[] = ArrayFunctions::pick_one([
            'Scroll of Fruit',
            'Scroll of the Sea',
            'Forgetting Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return $this->countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/outrageously-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOutrageouslyStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/outrageously-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Gold Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Major Scroll of Riches',
            'Dumbbell',
        ];

        $items[] = ArrayFunctions::pick_one([
            'Weird, Blue Egg',
            'Unexpectedly-familiar Metal Box',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/tower/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openTowerBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/tower/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();

        $message = $user->getName() . ' got this from a Tower Chest.';

        $newInventory[] = $inventoryService->receiveItem('Ceremonial Trident', $user, $user, $message, $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Linens and Things', 'Gold Bar', 'Iron Key', 'White Cloth' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Renaming Scroll', 'Behatting Scroll', 'Major Scroll of Riches', 'Species Transmigration Serum' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem('Secret Seashell', $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the chest revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/fishBag/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFishBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fishBag/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();

        $message = $user->getName() . ' got this from a Fish Bag.';

        $newInventory[] = $inventoryService->receiveItem('Fish', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Fish', $user, $user, $message, $location, $inventory->getLockedToOwner());

        $otherDrops = [
            'Fish',
            'Fish',
            'Silica Grounds',
            'Silica Grounds',
            'Crooked Stick',
            'Sand Dollar',
            'Tentacle',
            'Mermaid Egg',
        ];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one($otherDrops), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 5) === 1)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Jellyfish Jelly', 'Small, Yellow Plastic Bucket' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if(mt_rand(1, 20) === 1)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Merchant Fish', 'Secret Seashell', 'Ceremonial Trident' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the bag revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/alicesSecret/{inventory}/teaTime", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function alicesSecretTeaTime(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/alicesSecret/#/teaTime');
        $this->validateHouseSpace($inventory, $inventoryService);

        $loot = [
            'Toadstool', 'Shortbread Cookies'
        ];

        for($i = 0; $i < 3; $i++)
        {
            $loot[] = ArrayFunctions::pick_one([
                'Coffee Bean Tea with Mammal Extract',
                'Ginger Tea',
                'Black Tea',
                'Sweet Tea with Mammal Extract',
            ]);
        }

        for($i = 0; $i < 2; $i++)
        {
            if(mt_rand(1, 5) === 1)
            {
                $loot[] = ArrayFunctions::pick_one([
                    'Dreamwalker\'s Tea', 'Yogurt Muffin',
                ]);
            }
            else
            {
                $loot[] = ArrayFunctions::pick_one([
                    'Toadstool', 'Mini Chocolate Chip Cookies', 'Pumpkin Bread',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/alicesSecret/{inventory}/hourglass", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function alicesSecretHourglass(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/alicesSecret/#/hourglass');

        $item = $inventoryService->receiveItem('Hourglass', $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/alicesSecret/{inventory}/cards", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function alicesSecretCards(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/alicesSecret/#/cards');
        $this->validateHouseSpace($inventory, $inventoryService);

        $loot = [
            'Paper', 'Paper', 'Paper', 'Paper', ArrayFunctions::pick_one([ 'Paper', 'Quinacridone Magenta Dye' ])
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Inside Alice\'s Secret, you find some cards? Oh, wait, no: it\'s just', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/bobsSecret/{inventory}/fish", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function bobsSecretFish(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bobsSecret/#/fish');
        $this->validateHouseSpace($inventory, $inventoryService);

        $loot = [
            'Fish',
            'Fish',
            'Scales'
        ];

        for($i = 0; $i < 3; $i++)
        {
            if(mt_rand(1, 5) === 1)
            {
                $loot[] = ArrayFunctions::pick_one([
                    'Sand Dollar', 'Tentacle',
                ]);
            }
            else
            {
                $loot[] = ArrayFunctions::pick_one([
                    'Fish', 'Scales',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/bobsSecret/{inventory}/tool", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function bobsTool(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        EnchantmentRepository $enchantmentRepository, UserStatsRepository $userStatsRepository,
        ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bobsSecret/#/tool');
        $this->validateHouseSpace($inventory, $inventoryService);

        // apply "Bob's" bonus
        $tool = ArrayFunctions::pick_one([
            'Iron Tongs',
            'Garden Shovel',
            'Crooked Fishing Rod',
            'Yellow Scissors',
            'Small Plastic Bucket',
            'Straw Broom',
        ]);

        $item = $inventoryService->receiveItem($tool, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $item->setEnchantment(
            $enchantmentRepository->findOneByName('Bob\'s')
        );

        return $this->countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/bobsSecret/{inventory}/bbq", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function bobsBBQ(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, ToolBonusService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bobsSecret/#/bbq');
        $this->validateHouseSpace($inventory, $inventoryService);

        $loot = [
            'Charcoal',
            'Hot Dog',
            'Grilled Fish',
            'Tomato Ketchup',
            'Hot Potato'
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }
}
