<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\GrammarFunctions;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BookstoreService
{
    private $userStatsRepository;
    private $userQuestRepository;
    private $inventoryService;
    private $itemRepository;

    const BOOKSTORE_QUEST_NAME = 'Items Given to Bookstore';

    const QUEST_STEPS = [
        [
            'askingFor' => [ 'Moth' ],
            'dialog' => 'Could you find a Moth for me? Sometimes they just flutter into the house, but you might also be lucky enough to have a pet that burps them!',
        ],
        [
            'askingFor' => [ 'Trout Yogurt' ],
            'dialog' => 'I\'m looking for Trout Yogurt. It may sound weird... well, I guess it kind of is. It\'s an acquired taste.',
        ],
        [
            'askingFor' => [ 'Sweet Coffee Bean Tea with Mammal Extract' ],
            'dialog' => 'Could you find some Sweet Coffee Bean Tea with Mammal Extract? It\'s kind of a specialty here on Poppy Seed Pets Island (or whatever we\'re calling this place...)',
        ],
        [
            'askingFor' => [ 'Upside-down Shiny Pail' ],
            'dialog' => 'Next on my list is an Upside-down Shiny Pail. You can find the right side-up kind in the Portal. (If you haven\'t unlocked the Portal, try rolling some dice!)',
        ],
        [
            'askingFor' => [ 'Single', 'Musical Scales' ],
            'dialog' => 'Have your pets joined any music bands yet? I\'m looking for a Single, or a Musical Scale.',
        ],
        [
            'askingFor' => [ 'Scroll of Flowers' ],
            'dialog' => 'I\'ve been looking for a Scroll of Flowers for a while... I don\'t suppose you have an extra?',
        ],
        [
            'askingFor' => [ 'Bizet Cake' ],
            'dialog' => 'Could you get a Bizet Cake? It\'s... another specialty of the island, I guess you could say.',
        ],
        [
            'askingFor' => [ 'Dark Matter' ],
            'dialog' => 'I\'m looking for Dark Matter... you might have a pet that poops some... or have encountered some bats that poop it? I guess there\'s no getting around the fact that it\'s poop.',
        ],
        [
            'askingFor' => [ 'Letter from the Library of Fire' ],
            'dialog' => 'Could you bring me a Letter from the Library of Fire? It can be found in your Fireplace... if you haven\'t already gotten one, though, definitely read one before bringing one to me!',
        ],
        [
            'askingFor' => [ 'Upside-down, Yellow Plastic Bucket' ],
            'dialog' => 'I\'m looking to expand my wardrobe. Could you get me an Upside-down, Yellow Plastic Bucket?',
        ],
        [
            'askingFor' => [ 'Moon Pearl' ],
            'dialog' => 'I\'d like to get a Moon Pearl... I don\'t suppose you\'ve found any?',
        ],
        [
            'askingFor' => [ 'Weird Beetle' ],
            'dialog' => 'Sometimes, when harvesting plants in the Greenhouse, your pets may find a Weird Beetle. Could you bring me one?',
        ],
        [
            'askingFor' => [ 'Box of Ores' ],
            'dialog' => 'I\'m looking for a Box of Ores. It\'s another object that can be found in the Portal.',
        ],
        [
            'askingFor' => [ 'Planetary Ring' ],
            'dialog' => 'Do you have any skilled astronomers? I ask because I\'m looking for Planetary Ring.',
        ],
        [
            'askingFor' => [ 'Century Egg' ],
            'dialog' => 'Remember when I asked you for some Trout Yogurt? Recently, I\'m looking for something that\'s even _more_ of an acquired taste: Century Egg!',
        ],
        [
            'askingFor' => [ 'EP' ],
            'dialog' => 'I\'m looking for an EP. Do you have any pets in skilled bands?',
        ],
        [
            'askingFor' => [ 'Heart Beetle' ],
            'dialog' => 'Oh, I\'d really like a Heart Beetle. They can only be found in the Crystal Heart Dimension...',
        ],
        [
            'askingFor' => [ 'Tiny Black Hole' ],
            'dialog' => 'I\'ve been looking for a Tiny Black Hole. Astronomy groups can discover them, sometimes...',
        ],
        [
            'askingFor' => [ 'LP' ],
            'dialog' => 'I\'m looking for an LP. Do you have any pets in REALLY skilled bands?',
        ],
        [
            'askingFor' => [ 'Weird, Blue Egg', 'Unexpectedly-familiar Metal Box' ],
            'dialog' => 'A lot of people (and pets) on the island go searching for Cetgueli\'s treasure... could you bring me one of his treasures? Either a Weird, Blue Egg, or an Unexpectedly-familiar Metal Box?',
        ],
        [
            'askingFor' => [ 'Really Big Leaf' ],
            'dialog' => 'I\'d like to get my hands on a Really Big Leaf. I don\'t suppose you\'ve planted a Magic Bean Stalk?',
        ],
        [
            'askingFor' => [ 'Spirit Polymorph Potion' ],
            'dialog' => 'Could you get me a Spirit Polymorph Potion? The recipe is apparently a bit hard to come by...',
        ]
    ];

    public function __construct(
        UserStatsRepository $userStatsRepository, UserQuestRepository $userQuestRepository,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        $this->userStatsRepository = $userStatsRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
    }

    public function getBookstoreQuestStep(int $step): ?array
    {
        if($step < count(self::QUEST_STEPS))
            return self::QUEST_STEPS[$step];
        else
            return null;
    }

    public function advanceBookstoreQuest(User $user, string $itemToGive)
    {
        if(!$this->renamingScrollAvailable($user))
            throw new UnprocessableEntityHttpException('What??');

        $bookstoreQuestStep = $this->userQuestRepository->findOrCreate($user, self::BOOKSTORE_QUEST_NAME, 0);

        $questStep = $this->getBookstoreQuestStep($bookstoreQuestStep->getValue());

        if(!$questStep)
            throw new UnprocessableEntityHttpException('You\'ve brought back everything I need! Thanks!');

        if(!in_array($itemToGive, $questStep['askingFor']))
            throw new UnprocessableEntityHttpException('That\'s not what I\'m looking for right now...');

        if($this->inventoryService->loseItem($itemToGive, $user, [ LocationEnum::HOME, LocationEnum::BASEMENT ], 1) === 0)
            throw new UnprocessableEntityHttpException('You don\'t seem to have ' . GrammarFunctions::indefiniteArticle($itemToGive) . ' ' . $itemToGive . '...');

        $bookstoreQuestStep->setValue($bookstoreQuestStep->getValue() + 1);
    }

    public function getAvailableInventory(User $user)
    {
        $bookPrices = [
            'Welcome Note' => 10, // remember: this item can be turned into plain paper
            'Unlocking the Secrets of Grandparoot' => 15,
            'Cooking 101' => 15,
        ];

        $flowersPurchased = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::FLOWERBOMBS_PURCHASED ]);

        if($flowersPurchased && $flowersPurchased->getValue() > 0)
            $bookPrices['Book of Flowers'] = 15;

        $cookedSomething = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::COOKED_SOMETHING ]);
        $itemsDonatedToMuseum = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::ITEMS_DONATED_TO_MUSEUM ]);

        if($cookedSomething)
        {
            if($cookedSomething->getValue() >= 5)
                $bookPrices['Candy-maker\'s Cookbook'] = 20;

            if($cookedSomething->getValue() >= 10)
                $bookPrices['Big Book of Baking'] = 25;

            if($cookedSomething->getValue() >= 20)
                $bookPrices['Fish Book'] = 20;

            if($cookedSomething->getValue() >= 25)
                $bookPrices['Book of Noods'] = 20;

            if($cookedSomething->getValue() >= 35)
                $bookPrices['Pie Recipes'] = 15;

            if($cookedSomething->getValue() >= 50)
            {
                $bookPrices['Milk: The Book'] = 30;
                $bookPrices['Fried'] = 25;
            }
        }

        if($user->getUnlockedFireplace())
        {
            $bookPrices['Melt'] = 25;
        }

        if($itemsDonatedToMuseum)
        {
            if($itemsDonatedToMuseum->getValue() >= 100)
            {
                $bookPrices['Basement Blueprint'] = 100;
                $bookPrices['The Umbra'] = 25;
            }

            if($itemsDonatedToMuseum->getValue() >= 150)
                $bookPrices['Electrical Engineering Textbook'] = 50;

            if($itemsDonatedToMuseum->getValue() >= 200)
                $bookPrices['SOUP'] = 25;
        }

        if($user->getGreenhouse() && $user->getGreenhouse()->getMaxPlants() > 6)
        {
            $bookPrices['Bird Bath Blueprint'] = 200;
        }

        if($this->renamingScrollAvailable($user))
        {
            $bookPrices['Renaming Scroll'] = $this->getRenamingScrollCost($user);
        }

        ksort($bookPrices);

        return $bookPrices;
    }

    public function getRenamingScrollCost(User $user)
    {
        // 800 -> 250
        $bookstoreQuestStep = $this->userQuestRepository->findOrCreate($user, self::BOOKSTORE_QUEST_NAME, 0);

        return max(250, 800 - $bookstoreQuestStep->getValue() * 25);
    }

    public function renamingScrollAvailable(User $user): bool
    {
        $petsBirthed = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::PETS_BIRTHED ]);
        $petsAdopted = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::PETS_ADOPTED ]);
        $petsAcquired = ($petsBirthed ? $petsBirthed->getValue() : 0) + ($petsAdopted ? $petsAdopted->getValue() / 10 : 0);

        return $petsAcquired >= 3;
    }

    public function getResponseData(User $user)
    {
        $bookPrices = $this->getAvailableInventory($user);

        $bookItems = $this->itemRepository->findBy([ 'name' => array_keys($bookPrices) ], [ 'name' => 'ASC' ]);

        $books = array_map(function(Item $bookItem) use($bookPrices) {
            return [
                'item' => $bookItem,
                'price' => $bookPrices[$bookItem->getName()]
            ];
        }, $bookItems);

        if($this->renamingScrollAvailable($user))
        {
            $bookstoreQuestStep = $this->userQuestRepository->findOrCreate($user, BookstoreService::BOOKSTORE_QUEST_NAME, 0);
            $quest = $this->getBookstoreQuestStep($bookstoreQuestStep->getValue());
        }
        else
            $quest = null;

        return [
            'books' => $books,
            'quest' => $quest,
        ];
    }
}
