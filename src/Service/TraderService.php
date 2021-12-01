<?php
namespace App\Service;

use App\Entity\Trader;
use App\Entity\TradesUnlocked;
use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\TradeGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\TradesUnlockedRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class TraderService
{
    private const TRADER_NAMES = [
        // Saffron
        'Azafrán', 'Zaeafran',

        // Platinum
        'Platin', 'Albalatin',

        // Taaffeite
        'Taaffeite',

        // Jade
        'Jade', 'Yù',

        // Vanilla
        'Vanille', 'Fanilana',

        // Mahlab
        'Mahlab',

        // Cardamom
        'Ilaayachee',

        // Kadupul Flower
        'Kadupul',

        // Orchid
        'Lánhuā',

        // Fugu
        'Fugu',

        // Wagyu
        'Wagyu',

        // Silk
        'Sī',

        // Truffle
        'Truffe', 'Sōnglù',

        // Caviar
        'Kyabia',

        // Salt
        'Salz', 'Milh', 'Sel',

        // Dyes
        'Purpura', 'Tekhelet', 'Murex',

        // Dekopon
        'Dekopon',

        // Kyoho
        'Kyoho',

        // Diamond
        'Almaznyye',

        // Ruby
        'Ruby', 'Rubis',
    ];

    private $itemRepository;
    private $inventoryService;
    private $calendarService;
    private $transactionService;
    private $tradesUnlockedRepository;
    private $squirrel3;
    private $inventoryRepository;

    public function __construct(
        ItemRepository $itemRepository, InventoryService $inventoryService, CalendarService $calendarService,
        TransactionService $transactionService, TradesUnlockedRepository $tradesUnlockedRepository, Squirrel3 $squirrel3,
        InventoryRepository $inventoryRepository
    )
    {
        $this->itemRepository = $itemRepository;
        $this->inventoryService = $inventoryService;
        $this->calendarService = $calendarService;
        $this->transactionService = $transactionService;
        $this->tradesUnlockedRepository = $tradesUnlockedRepository;
        $this->squirrel3 = $squirrel3;
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @return int[]
     */
    public function getUnlockedTradeGroups(User $user): array
    {
        $tradesUnlocked = $this->tradesUnlockedRepository->findBy([
            'user' => $user->getId()
        ]);

        return array_map(function(TradesUnlocked $tu) { return $tu->getTrades(); }, $tradesUnlocked);
    }

    /**
     * @return int[]
     */
    public function getLockedTradeGroups(User $user): array
    {
        return array_diff(TradeGroupEnum::getValues(), $this->getUnlockedTradeGroups($user));
    }

    public function getOfferById(User $user, string $id): ?TraderOffer
    {
        $offers = $this->getOffers($user);

        foreach($offers as $offerGroup)
        {
            $exchange = ArrayFunctions::find_one($offerGroup['trades'], function(TraderOffer $o) use($id) { return $o->id === $id; });

            if($exchange !== null)
                return $exchange;
        }

        return null;
    }

    public function getOffers(User $user): array
    {
        $quantities = $this->inventoryRepository->getInventoryQuantities($user, LocationEnum::HOME, 'name');

        $dailyOffers = [
            'title' => date('l'),
            'trades' => $this->getHolidayOffers($user, $quantities)
        ];

        $offers = [
            [
                'title' => 'Food',
                'trades' => $this->getFoodsOffers($user, $quantities)
            ]
        ];

        $tradeGroups = $this->getUnlockedTradeGroups($user);

        foreach($tradeGroups as $group)
        {
            switch($group)
            {
                case TradeGroupEnum::METALS:
                    $title = 'Metals';
                    $trades = $this->getMetalOffers($user, $quantities);
                    break;
                case TradeGroupEnum::DARK_THINGS:
                    $title = 'Umbral';
                    $trades = $this->getDarkThingsOffers($user, $quantities);
                    break;
                case TradeGroupEnum::CURIOSITIES:
                    $title = 'Curiosities';
                    $trades = $this->getCuriositiesOffers($user, $quantities);
                    break;
                case TradeGroupEnum::PLUSHIES:
                    $title = 'Plushies';
                    $trades = $this->getPlushyOffers($user, $quantities);
                    break;
                case TradeGroupEnum::GAMING:
                    $title = 'Portal';
                    $trades = $this->getGamingOffers($user, $quantities);
                    break;
                case TradeGroupEnum::BOX_BOX:
                    $title = 'Box-box';
                    $trades = $this->getBoxBoxOffers($user, $quantities);
                    break;
                case TradeGroupEnum::BLEACH:
                    $title = 'Bleach';
                    $trades = $this->getBleachOffers($user, $quantities);
                    break;
                case TradeGroupEnum::DIGITAL:
                    $title = 'Digital';
                    $trades = $this->getDigitalOffers($user, $quantities);
                    break;
                case 3: // old "FOODS" group unlock
                    continue 2; // why "2"? see https://www.php.net/manual/en/control-structures.continue.php >_>
                default:
                    throw new \Exception('You have unlocked trade group #' . $group . '... which does not exist. Ben should fix this.');
            }

            $offers[] = [
                'title' => $title,
                'trades' => $trades
            ];
        }

        usort($offers, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });

        array_unshift($offers, $dailyOffers);

        return $offers;
    }

    private function getBleachOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dragon Flag'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Flag'), 1) ],
                'Not the flag design you were looking for? You should try the Flag of Tell Samarzhoustia!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sun Flag'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Flag'), 1) ],
                'Not the flag design you were looking for? You should try the Flag of Tell Samarzhoustia!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Feathers'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Feathers'), 1) ],
                'Sometimes it\'s just easier to defeat a demon than a pegasus, you know?',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Filthy Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(5)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Cloth'), 1) ],
                'There you go! Good as new!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Chocolate-stained Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(5)
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Cloth'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Cocoa Powder'), 1),
                ],
                'There you go! Good as new! And I even kept the Cocoa Powder for you.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Baabble'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(25),
                    TraderOfferCostOrYield::createMoney(25),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Baabble'), 1) ],
                'Just don\'t tell the satyrs I did this for you. I\'m pretty sure they\'d consider it cheating...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red Firework'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Firework'), 1) ],
                'There\'s a little Tell Samarzhoustia chemistry for you!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blue Firework'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Firework'), 1) ],
                'There\'s a little Tell Samarzhoustia chemistry for you!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Top Hat'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bright Top Hat'), 1) ],
                'Huh: the band bleached out to be purple? Must be some kind of powerful dye they used, there...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Jolliest Roger'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Creamiest Roger'), 1) ],
                'Well, it came out kind of funny. I think I liked the red better, myself, but hey: whatever floats your pirate boat!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paint Stripper'), 1) ],
                'I\'ve been getting some requests to "bleach" painted items. That\'s not really how that works, but I figured I could at least sell you guys some Paint Stripper, so you can do it yourself, if you want.',
                $user,
                $quantities
            ),
        ];
    }

    private function getDigitalOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('NUL'), 5),
                    TraderOfferCostOrYield::createMoney(2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 1) ],
                'XORs are, like, one of the de facto currencies for those trading in Project-E.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Pointer'), 3),
                    TraderOfferCostOrYield::createMoney(1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 1) ],
                'XORs are, like, one of the de facto currencies for those trading in Project-E.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fruits & Veggies Box'), 1) ],
                'Enjoy the fruits and veggies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Baker\'s Box'), 1) ],
                'Enjoy the baking supplies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Handicrafts Supply Box'), 1) ],
                'Enjoy the crafting supplies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Hat Box'), 1) ],
                'Enjoy the hat!',
                $user,
                $quantities
            ),
        ];
    }

    private function getBoxBoxOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('This is Getting Ridiculous'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Flying Bindle'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Trifecta'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('l33t h4xx0r'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.',
                $user,
                $quantities
            ),
        ];
    }

    private function getGamingOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Sword'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Four-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And only four sides? That\'s just not right.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dragon Flag'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Six-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sun Flag'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Six-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glass Pendulum'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Eight-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And _eight_ sides?? It\'s unnatural.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createRecyclingPoints(20),
                    TraderOfferCostOrYield::createMoney(40)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Scroll of Dice'), 1) ],
                'To be honest, those dice kind of give me the willies. A whole scroll of them? Yeah, no thanks!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Key'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Key'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Key'), 1),
                    TraderOfferCostOrYield::createMoney(1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Key Ring'), 1) ],
                'Do enjoy!',
                $user,
                $quantities
            )
        ];
    }

    /**
     * @return TraderOffer[]
     */
    private function getHolidayOffers(User $user, array $quantities): array
    {
        $now = new \DateTimeImmutable();
        $dayOfWeek = $now->format('D');

        $offers = $this->getDayOfWeekTrades($user, $quantities, $dayOfWeek);

        if($this->calendarService->isStockingStuffingSeason())
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Talon'), 2) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Antlers'), 1) ],
                'These were cut from Antlerfish earlier this year. Catch and release, of course. The antlers grow back every year, mating season is already over, and the antlers actually make the fish easier targets for predators, so the system really works out for everyone! Well, except for Antlerfish predators, I suppose. They kinda\' lose out.',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(20) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tawny Ears'), 1) ],
                'I _guess_ these could be reindeer ears? That\'s my best guess, anyway. Oh: made of plastic and fluff, of course! They\'re not _real_ ears! That would be brutal.',
                $user,
                $quantities
            );
        }

        if($this->calendarService->isEaster())
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blue Plastic Egg'), 5)
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Chili Calamari'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Deep-fried Toad Legs'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fisherman\'s Pie'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tomato Soup'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Coffee Jelly'), 1),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Yellow Plastic Egg'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Spice Rack'), 3),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Pink Plastic Egg'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Hat Box'), 1),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );
        }

        if($now->format('M') === 'Oct')
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Talon'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Unicorn Horn'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... isosceles, though. (Isoscelic? Isoscelean? Whatever.)',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Toadstool'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tea Leaves'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tinfoil Hat'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... crinkly, though. And no plume!',
                $user,
                $quantities
            );
        }

        // talk like a pirate day
        if($this->calendarService->isTalkLikeAPirateDay())
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Scales'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Seaweed'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rusty Rapier'), 1),
                ],
                "If I had 10~~m~~ for every Rapier I found lying on the bottom of the ocean, I'd be a very wealthy fish!\n\nOhohohoho!",
                $user,
                $quantities
            );
        }

        if($this->calendarService->isMayThe4th())
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Photon'), 1),
                    TraderOfferCostOrYield::createMoney(100),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Lightpike'), 1),
                ],
                "Masters of the Lightpike can cut you down in one stab!",
                $user,
                $quantities
            );
        }

        return $offers;
    }

    private function getMetalOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1) ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1) ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),
        ];
    }

    private function getDarkThingsOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Charcoal'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dark Matter'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackberries'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Tea'), 2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackonite'), 1) ],
                'The technique for forging Blackonite is unknown, even to Tell Samarzhoustia. But we _have_ established trade with a creature that knows the secret. The Charcoal, etc, is essentially an offering.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fairy Ring'), 3),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quinacridone Magenta Dye'), 1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fairy Swarm'), 1) ],
                'Quinacridone Magenta is pretty valuable stuff. Naturally, King Nebuludwigula XIII has a few robes dyed that color.',
                $user,
                $quantities
            )
        ];
    }

    private function getFoodsOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createMoney(100) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Deed for Greenhouse Plot'), 1) ],
                "Oh, fun, a greenhouse! What kind of Kelp will you be gr-- oh. Right, I suppose you'll just be growing Landweed, and such.\n\nWell.\n\nHave fun with that, I suppose.",
                $user,
                $quantities,
                true
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Cooking Buddy'), 1) ],
                'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Composter'), 1) ],
                'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Painted Fishing Rod'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper Bag'), 1) ],
                'I just can\'t believe humans are allowed to carry a rod without a permit.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ice Fishing'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper Bag'), 2) ],
                'I just can\'t believe humans are allowed to carry a rod without a permit.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Limestone'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ginger'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Grandparoot'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Carrot'), 1),
                ],
                'Limestone is an important building material in Tell Samarzhoustia. We build beautiful palaces, and enormous chimera statues. Well, enormous by fish standards. You should visit, sometime.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Crystal Ball'), 1 ) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sweet Beet'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tomato'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Vinegar'), 1),
                ],
                'There\'s a lot of Silica Grounds in Tell Samarzhoustia, of course, but turning them into something beautiful is an expensive process.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fiberglass'), 1 ) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Corn'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Onion'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Spicy Peps'), 1),
                ],
                'There\'s a lot of Silica Grounds in Tell Samarzhoustia, of course, but turning them into something beautiful is an expensive process.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bean Milk'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper'), 1) ],
                'We make something similar in Tell Samarzhoustia, but your Land Beans have a subtler flavor that\'s really starting to catch on.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tofu'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper'), 3) ],
                'We make something similar in Tell Samarzhoustia, but your Land Beans have a subtler flavor that\'s really starting to catch on.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rock Candy'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Witch-hazel'), 1) ],
                'As you can imagine, Rock Candy doesn\'t last long in Tell Samarzhoustia. It has to be packaged - and consumed - very carefully. It\'s all a bit bougie, really. Not that I mind: trade\'s trade!',
                $user,
                $quantities
            ),
        ];
    }

    private function getCuriositiesOffers(User $user, array $quantities): array
    {
        $moonName = $this->squirrel3->rngNextFromArray([
            'Europa', 'Ganymede', 'Callisto', 'Mimas', 'Enceladus', 'Titan', 'Miranda', 'Umbriel', 'Triton'
        ]);

        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createMoney(1000) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Money Sink'), 1) ],
                'The Museum\'s curator insisted I make this offer...',
                $user,
                $quantities,
                true
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(1000) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Garbage Disposal'), 1) ],
                'The Museum\'s curator insisted I make this offer...',
                $user,
                $quantities,
                true
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Secret Seashell'), 20) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Level 2 Sword'), 1) ],
                'It\'s dangerous to go alone. Take this.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Baabble'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('3D Printer'), 1) ],
                'Please use this responsibly, human. The amount of Plastic ending up in the oceans these days is a bit troubling.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(400),
                    TraderOfferCostOrYield::createRecyclingPoints(200),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Submarine'), 1) ],
                'Derelict submarines sometimes drift into Tell Samarzhoustia. We fix \'em up, and sell them to land-dwellers such as yourself! Have fun!',
                $user,
                $quantities,
                true
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Music Note'), 7) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Musical Scales'), 1) ],
                'I don\'t mind letting you in on a little Tell Samarzhoustian secret: you can do this "trade" yourself at home. Just combine 7 Music Notes.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ring'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Planetary Ring'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Onion Rings'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('String'), 1)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rings on Strings'), 1) ],
                'This item feels kind of silly, doesn\'t it? Well, I suppose it\'s not for me to say. Oh, and thanks for the Onion Rings. I was feeling a little peckish.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Planetary Ring'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gravitational Waves'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Everice'), 1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Icy Moon'), 1) ],
                'Does that one look kind of like ' . $moonName . ' to you?',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Nón Lá'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Toadstool'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Money Sink'), 1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Nấm Lá'), 1) ],
                'The goblin shark that sold me this told me that "nấm" mean "mushroom". Seems kind of on-the-nose, to me, but that\'s goblin sharks for you.',
                $user,
                $quantities
            ),
        ];
    }

    private function getPlushyOffers(User $user, array $quantities)
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Peacock Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bulbun Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sneqo Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rainbow Dolphin Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Peacock Plushy'), 1) ],
                'Such a classic.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bulbun Plushy'), 1) ],
                'These make great body pillows. Well, I guess it is a bit small for you, though.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sneqo Plushy'), 1) ],
                'I don\'t know why these things are plushies. They have arms! Snakes shouldn\'t have arms!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(12),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rainbow Dolphin Plushy'), 1) ],
                'The extra 2~~m~~ covers a tax imposed by the Great Rainbow Dolphin Empire.',
                $user,
                $quantities
            ),
        ];
    }

    private function getDayOfWeekTrades(User $user, array $quantities, string $dayOfWeek): array
    {
        $offers = [];

        if($this->calendarService->isValentinesOrAdjacent())
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Twu Wuv'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(100),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Pink Bow'), 1),
                ],
                'Please enjoy the complimentary chocolate, and remember: all candies "recycled" during Valentine\'s are guaranteed to find their way to the Giving Tree, where any pet may collect them!',
                $user,
                $quantities,
                true
            );
        }

        $leapDay = $this->calendarService->isLeapDay();

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wings'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Crystal Ball'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                'I don\'t know where all the Monday hate comes from. Mondays are _great_: you get Moon Pearls on Mondays!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper Bag'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Canned Food'), 1)
                ],
                'I don\'t know where all the Monday hate comes from. Mondays are _great_: you get to spend Moon Pearls on Mondays!',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Tue' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Laser-guided Sword'), 1) ],
                [ TraderOfferCostOrYield::createMoney(50) ],
                'Did you know "Tuesday" is named for a god of combat, and justice?',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Wed' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Feathers'), 3),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1) ],
                'What\'s the theme of today\'s trade? I\'ll never tell!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Feathers'), 3) ],
                'What\'s the theme of today\'s trade? I\'ll never tell!',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Thu' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ceremonial Trident'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bag of Beans'), 1) ],
                $dayOfWeek === 'Thu' ? 'If it were up to me, today would be called Poseidon\'s Day, but it wasn\'t, so fine: Thor\'s Day it is, I guess.' : 'It\'s like all the rules go out the window on leap days!',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Fri' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wheat Flower'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Sorry: we couldn\'t get our flippers on actual Red Gold.',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rice Flower'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Sorry: we couldn\'t get our flippers on actual Red Gold.',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Sat' || $leapDay)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Flute'), 1) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Aging Powder'), 1)
                ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. They also told me to tell anyone who made this trade that you can combine Plain Yogurt, Creamy Milk, and Aging Powder to make _more_ Plain Yogurt. I told them that that\'s basically common knowledge at this point, but they insisted, so... yeah. There you go.',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 2) ],
                [ TraderOfferCostOrYield::createMoney(5) ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. I guess they like yogurt?',
                $user,
                $quantities
            );
        }

        if($dayOfWeek === 'Sun')
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Triangle'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Key'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Telescope'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!',
                $user,
                $quantities
            );
        }

        return $offers;
    }

    public function userCanMakeExchange(User $user, TraderOffer $exchange): bool
    {
        foreach($exchange->cost as $cost)
        {
            switch($cost->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    $quantity = $this->inventoryService->countInventory($user, $cost->item, LocationEnum::HOME);

                    if($quantity < $cost->quantity)
                        return false;

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($user->getMoneys() < $cost->quantity)
                        return false;

                    break;

                case CostOrYieldTypeEnum::RECYCLING_POINTS:
                    if($user->getRecyclePoints() < $cost->quantity)
                        return false;

                    break;

                default:
                    throw new \InvalidArgumentException('Unexpected cost type "' . $cost->type . '".');
            }
        }

        return true;
    }

    /**
     * CAREFUL: Also used by some items, to perform transmutations.
     */
    public function makeExchange(User $user, TraderOffer $exchange, string $itemDescription = 'Received by trading with the Trader.')
    {
        foreach($exchange->cost as $cost)
        {
            switch($cost->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    $quantity = $this->inventoryService->loseItem($cost->item, $user, LocationEnum::HOME, $cost->quantity);

                    if($quantity < $cost->quantity)
                        throw new \InvalidArgumentException('You do not have the items needed to make this exchange. (Expected ' . $cost->quantity . ' items; only found ' . $quantity . '.)');

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($user->getMoneys() < $cost->quantity)
                        throw new \InvalidArgumentException('You do not have the moneys needed to make this exchange.');

                    if($this->squirrel3->rngNextInt(1, 50) === 1)
                        $this->transactionService->spendMoney($user, $cost->quantity, 'Traded away at the Trader. (That\'s usually just called "buying", right?)');
                    else
                        $this->transactionService->spendMoney($user, $cost->quantity, 'Traded away at the Trader.');

                    break;

                case CostOrYieldTypeEnum::RECYCLING_POINTS:
                    if($user->getRecyclePoints() < $cost->quantity)
                        throw new \InvalidArgumentException('You do not have the ♺ needed to make this exchange.');

                    $user->increaseRecyclePoints(-$cost->quantity);

                    break;

                default:
                    throw new \InvalidArgumentException('Unexpected cost type "' . $cost->type . '".');
            }
        }

        foreach($exchange->yield as $yield)
        {
            switch($yield->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    for($i = 0; $i < $yield->quantity; $i++)
                        $this->inventoryService->receiveItem($yield->item, $user, null, $itemDescription, LocationEnum::HOME, $exchange->lockedToAccount);

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($this->squirrel3->rngNextInt(1, 50) === 1)
                        $this->transactionService->getMoney($user, $yield->quantity, 'Traded for at the Trader. (That\'s usually just called "selling", right?)');
                    else
                        $this->transactionService->getMoney($user, $yield->quantity, 'Traded for at the Trader.');

                    break;

                case CostOrYieldTypeEnum::RECYCLING_POINTS:
                    $user->increaseRecyclePoints($yield->quantity);
                    break;
            }
        }
    }

    function recolorTrader(Trader $trader)
    {
        $h1 = $this->squirrel3->rngNextInt(0, 255);
        $h2 = $this->squirrel3->rngNextInt(0, 255);
        $h3 = $this->squirrel3->rngNextInt(0, 255);

        $l2 = $this->squirrel3->rngNextInt($this->squirrel3->rngNextInt(40, 120), 150);

        $s3 = $this->squirrel3->rngNextInt($this->squirrel3->rngNextInt(0, 40), $this->squirrel3->rngNextInt(160, 255));
        $l3 = $this->squirrel3->rngNextInt($this->squirrel3->rngNextInt(0, 40), $this->squirrel3->rngNextInt(160, 255));

        if($h1 >= 30 && $h1 < 130) $h1 += 120;
        if($h2 >= 30 && $h2 < 130) $h2 += 120;
        if($h3 >= 60 && $h3 < 130) $h3 += 120;

        $h1h2TooClose = abs($h1 - $h2) <= 20;

        if($h1h2TooClose)
        {
            if($h2 > $h1)
            {
                if($h2 < 30)
                    $h2 = ($h2 - 40 + 256) % 256;
                else
                    $h2 = ($h2 + 20) % 256;
            }
            else if($h2 < $h1)
                $h2 = ($h2 - 20 + 256) % 256;
        }

        $trader
            ->setColorA(ColorFunctions::HSL2Hex($h1 / 256, $this->squirrel3->rngNextInt(56, 100) / 100, 0.46))
            ->setColorB(ColorFunctions::HSL2Hex($h2 / 256, $this->squirrel3->rngNextInt(56, 100) / 100, $l2 / 255))
            ->setColorC(ColorFunctions::HSL2Hex($h3 / 256, $s3 / 255, $l3 / 255))
        ;
    }

    function generateTrader(): Trader
    {
        $trader = (new Trader())
            ->setName($this->squirrel3->rngNextFromArray(self::TRADER_NAMES))
        ;

        $this->recolorTrader($trader);

        return $trader;
    }
}
