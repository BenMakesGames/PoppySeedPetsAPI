<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\ItemGroup;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\SpiceRepository;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/box")
 */
class BoxController extends AbstractController
{
    #[Route("/twilight/{box}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openTwilightBox(
        Inventory $box, ResponseService $responseService,
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $box, 'box/twilight/#/open');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $items = [ 'Grandparoot', 'Cobweb' ];

        $possibleItems = [
            'Quinacridone Magenta Dye', 'Moon Pearl', 'Jar of Fireflies', 'Quintessence',
            'Candle', 'Dreamwalker\'s Tea', 'Eggplant', 'Glowing Protojelly'
        ];

        shuffle($possibleItems);

        for($i = 0; $i < 4; $i++)
            $items[] = $possibleItems[$i];

        shuffle($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $box->getCreatedBy(), 'Found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($box);
        $em->flush();

        $message = 'Rummaging through the box, you find ' . ArrayFunctions::list_nice($items, ', ', ', aaaaaand... ') . '!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/ores/{box}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        PetExperienceService $petExperienceService, UserStatsService $userStatsRepository, EntityManagerInterface $em,
        IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $box, 'box/ores/#/loot');
        ItemControllerHelpers::validateLocationSpace($box, $em);

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
            $pets = $em->getRepository(Pet::class)->findBy([ 'owner' => $user, 'location' => PetLocationEnum::HOME ]);

            /** @var Pet $pet */
            $pet = count($pets) == 0 ? null : $squirrel3->rngNextFromArray($pets);

            $message .= "\n\nWait, what!? One of the rocks moved!";

            if($pet === null)
                $message .= "\n\nA lobster claw reached out from underneath and pinched you before scuttling away!";
            else
            {
                $inventoryService->receiveItem('Fish', $user, $box->getCreatedBy(), 'Found inside a lobster inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
                $changes = new PetChanges($pet);
                $pet->increaseEsteem(3);
                $message .= "\n\nA lobster claw reached out from underneath and tried to pinch you, but " . $pet->getName() . " stepped in and beat it up!\n\nThat was a little scary, but hey: +1 Fish meat!";

                $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, 'While ' . $user->getName() . ' was sifting through a box of ore, a lobster jumped out and tried to attack them! ' . $pet->getName() . ' stepped in and saved the day! It was a little scary, but hey: +1 Fish meat!')
                    ->setChanges($changes->compare($pet));

                $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            }
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/smallOres/{box}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openSmallOreBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $box, 'box/smallOres/#/loot');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $possibleOres = [
            'Iron Ore', 'Iron Ore', 'Iron Ore',
            'Silver Ore', 'Silver Ore', 'Silver Ore',
            'Gold Ore', 'Gold Ore',
            'XOR',
        ];

        $numberOfItems = $squirrel3->rngNextInt(1, 3) == 1 ? 2 : 3;

        for($i = 0; $i < $numberOfItems; $i++)
            $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleOres), $user, $box->getCreatedBy(), 'Found inside ' . $box->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

        $em->remove($box);

        $message = 'Sifting through the box, you found ' . $numberOfItems . ' good chunks of ore!';

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/box/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBoxBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/box/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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
                'Juice Box',
                'Twilight Box',
                'Nature Box',
                'Monster Box',
                'Pizza Box',
            ];

            if($squirrel3->rngNextInt(1, 2) === 0) // TODO: remove this entirely once we get to, like, 20 possible boxes??
                $possibleItems[] = [ $squirrel3->rngNextFromArray([ 'Cereal Box', 'Hat Box' ]) ];

            if($squirrel3->rngNextInt(1, 20) === 0)
            {
                $possibleItems[] = $squirrel3->rngNextFromArray([
                    '4th of July Box',
                    'New Year Box',
                    'Chinese New Year Box',
                    'Bastille Day Box',
                    'Cinco de Mayo Box',
                    'Awa Odori Box',
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

    #[Route("/cereal/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openCerealBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/cereal/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $newInventory = [];

        $location = $inventory->getLocation();

        $message = $user->getName() . ' got this from a Cereal Box.';

        $newInventory[] = $inventoryService->receiveItem('Corn', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Wheat', $user, $user, $message, $location, $inventory->getLockedToOwner());
        $newInventory[] = $inventoryService->receiveItem('Rice', $user, $user, $message, $location, $inventory->getLockedToOwner());

        for($i = 0; $i < 7; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Corn', 'Wheat', 'Rice' ]), $user, $user, $message, $location, $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bakers/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBakers(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bakers/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/fruits-n-veggies/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/fruits-n-veggies/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/nature/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openNatureBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/nature/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        /** @var Inventory[] $newInventory */
        $newInventory = [];

        $location = $inventory->getLocation();
        $spice = $inventory->getSpice();

        $quantities = [2, 3, 4];
        shuffle($quantities);

        for($i = 0; $i < $quantities[0]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Crooked Stick', 'Tea Leaves', 'Grandparoot', 'Wheat', 'Rice', 'Ginger', 'Spicy Peps', 'Red Clover' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[1]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Orange', 'Red', 'Blackberries', 'Blueberries', 'Cacao Fruit', 'Avocado' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[2]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet', 'Potato', 'Corn' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($spice)
        {
            shuffle($newInventory);

            for($i = 0; $i < count($newInventory) / 3; $i++)
                $newInventory[$i]->setSpice($spice);
        }

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/monster/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openMonsterBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/monster/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        /** @var Inventory[] $newInventory */
        $newInventory = [];

        $location = $inventory->getLocation();
        $spice = $inventory->getSpice();

        $quantities = [2, 3, 4];
        shuffle($quantities);

        for($i = 0; $i < $quantities[0]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Egg', 'Fluff', 'Feathers', 'Talon', 'Duck Sauce', 'Worms' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[1]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Fish', 'Scales', 'Toad Legs', 'Tentacle', 'Sand Dollar', 'Jellyfish Jelly' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        for($i = 0; $i < $quantities[2]; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Creamy Milk', 'Butter', 'Plain Yogurt', 'Oil', 'Mayo(nnaise)' ]), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($spice)
        {
            shuffle($newInventory);

            for($i = 0; $i < count($newInventory) / 3; $i++)
                $newInventory[$i]->setSpice($spice);
        }

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/handicrafts/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openHandicrafts(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/handicrafts/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/gaming/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openGamingBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/gaming/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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
            $rarityGroup = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => 'Hollow Earth Booster Pack: Rare' ]);
        else if($r >= 4)
            $rarityGroup = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => 'Hollow Earth Booster Pack: Uncommon' ]);
        else
            $rarityGroup = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => 'Hollow Earth Booster Pack: Common' ]);

        $tile = InventoryService::getRandomItemFromItemGroup($squirrel3, $rarityGroup);

        $newInventory[] = $inventoryService->receiveItem($tile, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        if($squirrel3->rngNextInt(1, 10) === 1)
            $newInventory[] = $inventoryService->receiveItem('Glowing Twenty-sided Die', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bagOfBeans/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBagOfBeans(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bagOfBeans/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('You upturn the bag, finding', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/pepperbox/{inventory}/disassemble", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function disassemblePepperbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/pepperbox/#/disassemble');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

    #[Route("/jukebox/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function listenToJukebox(
        Inventory $inventory, ResponseService $responseService, PetExperienceService $petExperienceService,
        EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/jukebox/#/listen');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $listenedToJukebox = $userQuestRepository->findOrCreate($user, 'Listened to Jukebox', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $listenedToJukebox->getValue())
            return $responseService->itemActionSuccess('You already listened to the Jukebox today. Everyone knows that Jukeboxes can only be listened to once per day.');

        if($inventory->getLocation() !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('This game doesn\'t have sound, so there wouldn\'t be much sense in playing the Jukebox for yourself. Try playing it at home, where your pets can hear. (It\'s not that there\'s no sound in Poppy Seed Pets, it\'s just that it\'s not manifested in the game interface. (Is this making sense?))');

        $listenedToJukebox->setValue($today);

        $pets = $em->getRepository(Pet::class)->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        $petNames = [];

        foreach($pets as $pet)
        {
            $petNames[] = $pet->getName();
            $changes = new PetChanges($pet);

            $pet->increaseSafety(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, $pet->getName() . ' listened to the Jukebox.');

            $petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ], $activityLog);

            $activityLog->setChanges($changes->compare($pet));
        }

        $em->flush();

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($petNames) . ' enjoyed listening to the Jukebox!');
    }

    #[Route("/sandbox/{inventory}/raid", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function raidSandbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/sandbox/#/raid');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

    #[Route("/paperBag/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openPaperBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/paperBag/#/open');

        $item = ItemRepository::findOneByName($em, $squirrel3->rngNextFromArray([
            'Apricot', 'Baking Soda', 'Beans', 'Blackberry Lassi', 'Blueberries', 'Butter', 'Canned Food', 'Celery',
            'Cockroach', 'Corn', 'Cream of Tartar', 'Creamy Milk', 'Egg', 'Fish', 'Fluff', 'Glowing Four-sided Die',
            'Grandparoot', 'Honeydont', 'Hot Dog', 'Iron Ore', 'Kombucha', 'Melon Bun', 'Mint', 'Mixed Nuts', 'Naner',
            'Oil', 'Onion', 'Orange', 'Pamplemousse', 'Plain Yogurt', 'Quintessence', 'Red', 'Red Clover', 'Rice',
            'Seaweed', 'Secret Seashell', 'Silica Grounds', 'Smallish Pumpkin', 'Sugar', 'Toad Legs', 'Tomato',
            'Wheat Flour', 'World\'s Best Sugar Cookie', 'Yeast', 'Yellowy Lime', 'Ponzu'
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

            if(
                $item->getName() !== 'Cockroach' && (
                    $openedStat->getValue() == 2 || (
                        $openedStat->getValue() >= 5 &&
                        $squirrel3->rngNextInt(1, 15) === 1
                    )
                )
            )
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

    #[Route("/july4/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open4thOfJulyBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/july4/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bastille/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBastilleDayBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bastille/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $lockedToOwner);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/may5/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openCincoDeMayoBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/may5/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $newInventory = [
            $inventoryService->receiveItem('Basic Fish Taco', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Spicy Peps', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Yellowy Lime', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Elote', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Music Note', $user, $user, $comment, $location, $lockedToOwner),
            $inventoryService->receiveItem('Red Firework', $user, $user, $comment, $location, $lockedToOwner),
        ];

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/newYear/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openNewYearBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/newYear/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/chineseNewYear/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openChineseNewYearBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/chineseNewYear/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/goldChest/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openGoldChest(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/goldChest/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = $inventoryRepository->findOneToConsume($user, 'Gold Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Gold Key to unlock a Gold Chest!');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $possibleItems = [
            'Major Scroll of Riches',
            'Minor Scroll of Riches',
            'Gold Triangle',
            'Tile: Gold Vein',
            '"Gold" Idol',
            'Gold Ring'
        ];

        $items = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);
        $items[] = 'Gold Bar';
        $items[] = 'Gold Bar';
        sort($items);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('You used a Gold Key to open the Gold Chest, and revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/rubyChest/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openRubyChest(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/rubyChest/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $possibleItems = [
            'Ruby Feather',
            'Key Ring',
            'Major Scroll of Riches',
            'Blackonite',
            'Hollow Earth Booster Pack',
            'Magic Hourglass',
        ];

        $items = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);
        $items[] = 'Quintessence';
        $items[] = 'Quintessence';
        sort($items);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('You opened the Ruby Chest... whoa: it\'s got', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/tower/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openTowerBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/tower/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the chest revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/fishBag/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openFishBag(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/fishBag/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

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

        return BoxHelpers::countRemoveFlushAndRespond('Opening the bag revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/chocolate/{box}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openChocolateChest(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $box, 'box/chocolate/#/open');
        ItemControllerHelpers::validateLocationSpace($box, $em);

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        /** @var Pet $pet */
        $pet = $squirrel3->rngNextFromArray(
            $em->getRepository(Pet::class)->findBy([ 'owner' => $user, 'location' => PetLocationEnum::HOME ])
        );

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
        $grossItem->setSpice(SpiceRepository::findOneByName($em, 'Chocolate-covered'));
        $lootNames[] = 'a Chocolate-covered ' . $grossItem->getItem()->getName();

        $em->remove($box);

        sort($lootNames);

        $description .= 'Inside the chest, you find ' . ArrayFunctions::list_nice($lootNames) . '!';

        $em->flush();

        return $responseService->itemActionSuccess($description, [ 'itemDeleted' => true ]);
    }
}
