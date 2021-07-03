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
use App\Repository\ItemGroupRepository;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\InventoryModifierService;
use App\Service\Squirrel3;
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
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($box, 'box/hat/#/open');

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $hatItem = $itemRepository->findOneByName($squirrel3->rngNextFromArray([
            'Masquerade Mask',
            'Merchant\'s Cap',
            'Wizarding Hat',
            'Gray Bow',
            'Cool Sunglasses',
            'Sombrero',
            'Judy',
            'Propeller Beanie',
        ]));

        $userStatsRepository->incrementStat($user, 'Opened ' . $box->getItem()->getNameWithArticle());

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

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/ores/{box}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, PetRepository $petRepository,
        PetExperienceService $petExperienceService, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        Squirrel3 $squirrel3
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

        $stat = $userStatsRepository->incrementStat($user, 'Looted ' . $box->getItem()->getNameWithArticle());

        $numberOfItems = $squirrel3->rngNextInt(3, 5);
        $containsLobster = $stat->getValue() === 1 || $squirrel3->rngNextInt(1, 4) === 1;

        if($containsLobster)
            $numberOfItems = max(3, $numberOfItems - 1);

        for($i = 0; $i < $numberOfItems; $i++)
            $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleOres), $user, $box->getCreatedBy(), 'Found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($box);

        $message = 'Sifting through the box, you found ' . $numberOfItems . ' good chunks of ore!';

        if($containsLobster)
        {
            /** @var Pet $pet */
            $pet = $squirrel3->rngNextFromArray($petRepository->findBy([ 'owner' => $user, 'inDaycare' => false ]));

            $message .= "\n\nWait, what!? One of the rocks moved!";

            if($pet === null)
                $message .= "\n\nA lobster claw reached out from underneath and pinched you before scuttling away!";
            else
            {
                $inventoryService->receiveItem('Fish', $user, $box->getCreatedBy(), 'Found inside a lobster inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
                $changes = new PetChanges($pet);
                $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem(3);
                $message .= "\n\nA lobster claw reached out from underneath and tried to pinch you, but " . $pet->getName() . " stepped in and beat it up!\n\nThat was a little scary, but hey: +1 Fish meat!";
                $responseService->createActivityLog($pet, 'While ' . $user->getName() . ' was sifting through a box of ore, a lobster jumped out and tried to attack them! ' . $pet->getName() . ' stepped in and saved the day! It was a little scary, but hey: +1 Fish meat!', '', $changes->compare($pet));
            }
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/box/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBoxBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/box/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        if($squirrel3->rngNextInt(1, 50) === 1)
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

            if($squirrel3->rngNextInt(1, 4) === 0)
                $possibleItems[] = [ $squirrel3->rngNextFromArray([ 'Cereal Box', 'Hat Box' ]) ];

            if($squirrel3->rngNextInt(1, 20) === 0)
            {
                $possibleItems[] = $squirrel3->rngNextFromArray([
                    '4th of July Box',
                    'New Year Box',
                    'Chinese New Year Box',
                    'Bastille Day Box',
                    // TODO: other holiday boxes
                ]);
            }

            shuffle($possibleItems);

            $message = "What boxes will be in _this_ Box Box, I wonder?\n\nOh: " . $possibleItems[0] . " and " . $possibleItems[1] . ", apparently!";

            $inventoryService->receiveItem($possibleItems[0], $user, $user, $user->getName() . ' found this in a Box Box.', $location, $inventory->getLockedToOwner());
            $inventoryService->receiveItem($possibleItems[1], $user, $user, $user->getName() . ' found this in a Box Box.', $location, $inventory->getLockedToOwner());
        }

        $userStatsRepository->incrementStat($user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/cereal/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openCerealBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
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
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Corn', 'Wheat', 'Rice' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/bakers/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBakers(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository,
        InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bakers/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $location = $inventory->getLocation();
        $spice = $inventory->getSpice();

        $freeBasicRecipes = $userQuestRepository->findOrCreate($user, 'Got free Basic Recipes', false);
        if(!$freeBasicRecipes->getValue())
        {
            $newInventory[] = $inventoryService->receiveItem('Cooking 101', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());
            $freeBasicRecipes->setValue(true);
        }

        $newInventory[] = $inventoryService->receiveItem('Wheat', $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Egg', 'Wheat Flour', 'Sugar', 'Creamy Milk' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Corn Syrup', 'Yeast', 'Cocoa Beans', 'Baking Soda', 'Cream of Tartar' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 4) === 1)
            $newInventory[] = $inventoryService->receiveItem('Cobbler Recipe', $user, $user, $user->getName() . ' got this from a weekly Care Package.', $location, $inventory->getLockedToOwner());

        if($spice)
        {
            $squirrel3->rngNextShuffle($newInventory);

            for($i = 0; $i < count($newInventory) / 3; $i++)
                $newInventory[$i]->setSpice($spice);
        }

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/fruits-n-veggies/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository,
        InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fruits-n-veggies/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        /** @var Inventory[] $newInventory */
        $newInventory = [];

        $location = $inventory->getLocation();
        $spice = $inventory->getSpice();

        $freeBasicRecipes = $userQuestRepository->findOrCreate($user, 'Got free Basic Recipes', false);
        if(!$freeBasicRecipes->getValue())
        {
            $newInventory[] = $inventoryService->receiveItem('Cooking 101', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());
            $freeBasicRecipes->setValue(true);
        }

        // guaranteed orange or red
        $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Orange', 'Red' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Orange', 'Red', 'Blackberries', 'Blueberries' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($spice)
        {
            shuffle($newInventory);

            for($i = 0; $i < count($newInventory) / 3; $i++)
                $newInventory[$i]->setSpice($spice);
        }

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/handicrafts/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openHandicrafts(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/handicrafts/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner()),
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner())
        ];

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray(['Fluff', 'Plastic', 'Green Dye', 'Yellow Dye', 'Paper', 'Glue']), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 3; $i++)
        {
            $itemName = $squirrel3->rngNextFromArray([ 'Limestone', 'Glass', 'Iron Bar', 'Iron Ore', 'Silver Ore' ]);

            if($itemName === 'Limestone')
                $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '. I don\'t know how it fit in there, either. Your guess is as good as mine.';
            else
                $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

            $newInventory[] = $inventoryService->receiveItem($itemName, $user, $user, $description, $location, $inventory->getLockedToOwner());
        }

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/gaming/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openGamingBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService,
        ItemGroupRepository $itemGroupRepository
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
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($dice), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        // one tile
        $r = $squirrel3->rngNextInt(1, 6);

        if($r === 6)
            $rarityGroup = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Rare');
        else if($r >= 4)
            $rarityGroup = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Uncommon');
        else
            $rarityGroup = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Common');

        $tile = $inventoryService->getRandomItemFromItemGroup($rarityGroup);

        $newInventory[] = $inventoryService->receiveItem($tile, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem('Glowing Twenty-sided Die', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @param Inventory[] $newInventory
     */
    private function countRemoveFlushAndRespond(
        string $messagePrefix,
        UserStatsRepository $userStatsRepository, User $user, Inventory $inventory, array $newInventory,
        ResponseService $responseService, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    ): JsonResponse
    {
        $userStatsRepository->incrementStat($user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        $itemList = array_map(fn(Inventory $i) => $toolBonusService->getNameWithModifiers($i), $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($messagePrefix . ' ' . ArrayFunctions::list_nice($itemList) . '.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/bagOfBeans/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBagOfBeans(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bagOfBeans/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $newInventory = [];

        $beans = $squirrel3->rngNextInt(6, $squirrel3->rngNextInt(7, 12));

        $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $beans; $i++)
        {
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray(['Coffee Beans', 'Cocoa Beans', 'Beans']), $user, $user, $description, $location, $inventory->getLockedToOwner())
                ->setSpice($inventory->getSpice())
            ;
        }

        return $this->countRemoveFlushAndRespond('You upturn the bag, finding', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/pepperbox/{inventory}/disassemble", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function disassemblePepperbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/pepperbox/#/disassemble');
        $this->validateHouseSpace($inventory, $inventoryService);

        $peppers = $squirrel3->rngNextInt(4, $squirrel3->rngNextInt(6, 10));

        $description = $user->getName() . ' got this by taking apart ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();

        for($i = 0; $i < $peppers; $i++)
        {
            $inventoryService->receiveItem('Spicy Peps', $user, $user, $description, $location, $inventory->getLockedToOwner())
                ->setSpice($inventory->getSpice())
            ;
        }

        $userStatsRepository->incrementStat($user, 'Disassembled ' . $inventory->getItem()->getNameWithArticle());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take apart the Pepperbox into its constituent pieces...', [ 'itemDeleted' => true ]);
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
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/sandbox/#/raid');
        $this->validateHouseSpace($inventory, $inventoryService);

        $sand = $squirrel3->rngNextInt(6, $squirrel3->rngNextInt(7, 12));

        $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();

        $a = $squirrel3->rngNextInt(1, 100);
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

        $userStatsRepository->incrementStat($user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($itemActionMessage, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/paperBag/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openPaperBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/paperBag/#/open');

        $item = $itemRepository->findOneByName($squirrel3->rngNextFromArray([
            'Apricot', 'Yeast', 'Baking Soda', 'Beans', 'Blackberry Lassi', 'Blueberries', 'Butter', 'Celery',
            'Cockroach', 'Corn', 'Cream of Tartar', 'Creamy Milk', 'Dark Matter', 'Egg', 'Fish', 'Fluff',
            'Grandparoot', 'Honeydont', 'Hot Dog', 'Iron Ore', 'Kombucha', 'Melon Bun', 'Naner', 'Oil', 'Onion',
            'Orange', 'Pamplemousse', 'Plain Yogurt', 'Quintessence', 'Red', 'Rice', 'Seaweed', 'Secret Seashell',
            'Silica Grounds', 'Smallish Pumpkin', 'Sugar', 'Toad Legs', 'Tomato', 'Wheat Flour',
            'World\'s Best Sugar Cookie', 'Glowing Four-sided Die', 'Mint', 'Mixed Nuts', 'Canned Food',
        ]));

        $openedStat = $userStatsRepository->incrementStat($user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        if($item->getName() === 'Cockroach' && $squirrel3->rngNextInt(1, 3) === 1)
        {
            $numRoaches = $squirrel3->rngNextInt(6, 8);

            for($i = 0; $i < $numRoaches; $i++)
            {
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner())
                    ->setSpice($inventory->getSpice())
                ;
            }

            $message = 'You open the bag... agh! It\'s swarming with roaches!!';
        }
        else
        {
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner())
                ->setSpice($inventory->getSpice())
            ;

            if($item->getName() !== 'Cockroach' && $openedStat->getValue() >= 5 && $squirrel3->rngNextInt(1, 15) === 1)
            {
                $message = $squirrel3->rngNextFromArray([
                    'You open the bag... WHAT THE FU-- oh, wait, nm: it\'s just ' . $item->getNameWithArticle() . '.',
                    'Da, da, da, DAAAAAAAA: ' . $item->getNameWithArticle() . '!',
                    'You open the bag... just some ordinary, run-of-the-mill ' . $item->getName() . '.',
                    'You open the bag... there\'s another Paper Bag inside, so you open _that_... ah! ' . ucfirst($item->getNameWithArticle()) . '! (Finally!)',
                    'You open the bag... ah! It\'s the ' . $item->getName() . ' you\'ve always dreamed about; the ' . $item->getName() . ' you _so richly deserve_.',
                    'You open the bag... ah! ' . ucfirst($item->getNameWithArticle()) . ' fit for a queen! Or a king! Or whatever! You do you!',
                    'You open the bag... it\'s empty??? Wait, no, here it is, stuck under a flap in the deepest recesses of the bag: ' . $item->getNameWithArticle() . '.',
                    'You open the bag... you pray it\'s not ' . $item->getNameWithArticle() . ', but - and I hate to break it to you - that\'s _exactly_ what it is.',
                    "You open the-- aw, shit! The bag tore!\n\nSomething tumbles out, and makes a very uncomfortable noise when it hit the ground. Well, at least it didn't hit you on its way there.\n\nYou look past the bag, to the floor, and at the source of your consternation. Hmph: all that trouble for " . $item->getNameWithArticle() . "...",
                    'You open the bag... iiiiiiiiiiit\'s ' . $item->getNameWithArticle() . '.',
                    "If I tell you there's " . $item->getNameWithArticle() . " in this bag, will you believe me?\n\nThere's " . $item->getNameWithArticle() . " in this bag.",
                    "You open the bag... but it's one of those Mimic Paper Bags! OH NO! IT CHOMPS DOWN HARD ON YOUR-- oh. Wait, it... it doesn't have any teeth.\n\nWell, it's a bit more work - and a bit wetter - than you\'d like, but with a little work you manage to extract " . $item->getNameWithArticle() . ".",
                    'You open the bag... but it\'s one of those Mimic Paper Bags! OH NO! It wriggles free, drops to the ground, and scurries off, ' . $item->getNameWithArticle() . ' tumbling out of its... mouth (???) as it goes.',
                    "You open the bag... for some reason it's got that insulation lining on the inside? " . $squirrel3->rngNextFromArray([ 'Cold', 'Warm' ]) . " air cascades out of the bag as you rummage around inside...\n\nAh, here it is: " . $item->getNameWithArticle() . "!",
                    "You try to open the bag, but it's glued shut!\n\nFoolish bag! Do you really think you're a match for " . $user->getName() . "'s RIPPLING, _SEXY_ MUSCLES!?!\n\nRAWR!!\n\nThe bag is torn in two, sending " . $item->getNameWithArticle() . " tumbling to the ground.",
                ]);
            }
            else
                $message = 'You open the bag... ah! ' . ucfirst($item->getNameWithArticle()) . '!';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/july4/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open4thOfJulyBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/july4/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

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
     * @Route("/bastille/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBastilleDayBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService,
        Squirrel3 $rng
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bastille/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $items = [
            $rng->rngNextFromArray([ 'Red Firework', 'White Firework', 'Blue Firework' ]),
            'Frites', 'Frites',
            'Music Note',
            'Slice of Flan Pâtissier',
            'Sweet Roll',
            'Berry Cobbler',
        ];

        $newInventory = [];

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $lockedToOwner),

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/newYear/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openNewYearBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/newYear/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

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

        for($x = $squirrel3->rngNextInt(4, 5); $x > 0; $x--)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($alcohol), $user, $user, $comment, $location, $lockedToOwner);

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/chineseNewYear/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openChineseNewYearBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/chineseNewYear/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $newInventory = [
            $inventoryService->receiveItem('Yellow Firework', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Orange', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Zongzi', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Gold Dragon Ingot', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Gold Dragon Ingot', $user, $user, $comment, $location, $lockedToOwner),
        ];

        return $this->countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/little-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openLittleStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/little-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Iron Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $squirrel3->rngNextInt(10, $squirrel3->rngNextInt(20, $squirrel3->rngNextInt(50, $squirrel3->rngNextInt(100, 200)))); // averages 35?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $possibleItems = [
            'Silver Bar', 'Silver Bar',
            'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Blackberry Wine',
            'Fluff',
            'Glowing Six-sided Die',
        ];

        $numItems = $squirrel3->rngNextInt(2, $squirrel3->rngNextInt(3, 4));
        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < $numItems; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItems), $user, $user, $comment, $location);

        $newInventory[] = $inventoryService->receiveItem('Piece of Cetgueli\'s Map', $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return $this->countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/very-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/very-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Silver Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $squirrel3->rngNextInt(15, $squirrel3->rngNextInt(45, $squirrel3->rngNextInt(100, $squirrel3->rngNextInt(200, 300)))); // averages 50?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $items = [
            'Silver Bar',
            'Gold Bar',
            'Gold Bar',
            'Glowing Six-sided Die',
        ];

        $items[] = $squirrel3->rngNextFromArray([
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Pepperbox',
        ]);

        $items[] = $squirrel3->rngNextFromArray([
            'Minor Scroll of Riches',
            'Magic Hourglass',
        ]);

        $items[] = $squirrel3->rngNextFromArray([
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
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/outrageously-strongbox/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Gold Key');

        if(!$key)
            throw new UnprocessableEntityHttpException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Major Scroll of Riches',
            'Dumbbell',
        ];

        $items[] = $squirrel3->rngNextFromArray([
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
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
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
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Linens and Things', 'Gold Bar', 'Iron Key', 'White Cloth' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Renaming Scroll', 'Behatting Scroll', 'Major Scroll of Riches', 'Species Transmigration Serum' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem('Secret Seashell', $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the chest revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/fishBag/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFishBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryModifierService $toolBonusService
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
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($otherDrops), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 5) === 1)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Jellyfish Jelly', 'Small, Yellow Plastic Bucket' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 20) === 1)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Merchant Fish', 'Secret Seashell', 'Ceremonial Trident' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return $this->countRemoveFlushAndRespond('Opening the bag revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em, $toolBonusService);
    }

    /**
     * @Route("/alicesSecret/{inventory}/teaTime", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function alicesSecretTeaTime(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, InventoryModifierService $toolBonusService
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
            $loot[] = $squirrel3->rngNextFromArray([
                'Coffee Bean Tea with Mammal Extract',
                'Ginger Tea',
                'Black Tea',
                'Sweet Tea with Mammal Extract',
            ]);
        }

        for($i = 0; $i < 2; $i++)
        {
            if($squirrel3->rngNextInt(1, 5) === 1)
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Dreamwalker\'s Tea', 'Yogurt Muffin',
                ]);
            }
            else
            {
                $loot[] = $squirrel3->rngNextFromArray([
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
        UserStatsRepository $userStatsRepository, ResponseService $responseService, InventoryModifierService $toolBonusService
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
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/alicesSecret/#/cards');
        $this->validateHouseSpace($inventory, $inventoryService);

        $loot = [
            'Paper', 'Paper', 'Paper', 'Paper', $squirrel3->rngNextFromArray([ 'Paper', 'Quinacridone Magenta Dye' ])
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
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, ResponseService $responseService, InventoryModifierService $toolBonusService
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
            if($squirrel3->rngNextInt(1, 5) === 1)
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Sand Dollar', 'Tentacle',
                ]);
            }
            else
            {
                $loot[] = $squirrel3->rngNextFromArray([
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
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        EnchantmentRepository $enchantmentRepository, UserStatsRepository $userStatsRepository,
        ResponseService $responseService, InventoryModifierService $toolBonusService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bobsSecret/#/tool');
        $this->validateHouseSpace($inventory, $inventoryService);

        // apply "Bob's" bonus
        $tool = $squirrel3->rngNextFromArray([
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
        UserStatsRepository $userStatsRepository, ResponseService $responseService, InventoryModifierService $toolBonusService
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

    /**
     * @Route("/chocolate/{box}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openChocolateChest(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, PetRepository $petRepository,
        SpiceRepository $spiceRepository, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($box, 'box/chocolate/#/open');
        $this->validateHouseSpace($box, $inventoryService);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        /** @var Pet $pet */
        $pet = $squirrel3->rngNextFromArray($petRepository->findBy([ 'owner' => $user, 'inDaycare' => false ]));

        if($pet)
        {
            $description = 'The chest is locked. You struggle with it for a bit before ' . $pet->getName() . ' simply eats the lock. ';
            $pet
                ->increaseFood($squirrel3->rngNextInt(2, 4))
                ->increaseEsteem(2)
            ;
        }
        else
        {
            $description = 'The chest is locked... so you eat the lock. ';
        }

        $possibleItems = [
            'Chocolate Bar', 'Chocolate Sword', 'Chocolate Cake Pops', 'Chocolate Meringue', 'Chocolate Syrup',
            'Chocolate Toffee Matzah', 'Chocolate-covered Honeycomb', 'Chocolate-frosted Donut',
            'Mini Chocolate Chip Cookies', 'Slice of Chocolate Cream Pie', 'Chocolate Key'
        ];

        $userStatsRepository->incrementStat($user, 'Looted ' . $box->getItem()->getNameWithArticle());

        $numberOfItems = $squirrel3->rngNextInt(2, 4);

        for($i = 0; $i < $numberOfItems; $i++)
        {
            $item = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItems), $user, $box->getCreatedBy(), $user->getName() . ' found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

            $lootNames[] = $item->getItem()->getNameWithArticle();
        }

        $possibleGrossItems = [
            'Tomato "Sushi"', 'Tentacle', 'Hot Dog', 'Gefilte Fish', 'Egg Salad', 'Minestrone', 'Carrot Juice',
            'Onion Rings', 'Iron Sword',
        ];

        $grossItem = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleGrossItems), $user, $box->getCreatedBy(), $user->getName() . ' found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
        $grossItem->setSpice($spiceRepository->findOneByName('Chocolate-covered'));
        $lootNames[] = 'a Chocolate-covered ' . $grossItem->getItem()->getName();

        $em->remove($box);

        sort($lootNames);

        $description .= 'Inside the chest, you find ' . ArrayFunctions::list_nice($lootNames) . '!';

        $em->flush();

        return $responseService->itemActionSuccess($description, [ 'itemDeleted' => true ]);
    }
}
