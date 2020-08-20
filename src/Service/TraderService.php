<?php
namespace App\Service;

use App\Entity\Trader;
use App\Entity\TradesUnlocked;
use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\TradeEnum;
use App\Enum\TradeGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
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
    private $userStatsRepository;
    private $calendarService;
    private $userQuestRepository;
    private $transactionService;
    private $tradesUnlockedRepository;

    public function __construct(
        ItemRepository $itemRepository, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        CalendarService $calendarService, UserQuestRepository $userQuestRepository, TransactionService $transactionService,
        TradesUnlockedRepository $tradesUnlockedRepository
    )
    {
        $this->itemRepository = $itemRepository;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->calendarService = $calendarService;
        $this->userQuestRepository = $userQuestRepository;
        $this->transactionService = $transactionService;
        $this->tradesUnlockedRepository = $tradesUnlockedRepository;
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

    public function getOfferById(User $user, string $id): TraderOffer
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
        $offers = [
            [
                'title' => date('l'),
                'trades' => $this->getHolidayOffers($user)
            ]
        ];

        $tradeGroups = $this->getUnlockedTradeGroups($user);

        foreach($tradeGroups as $group)
        {
            switch($group)
            {
                case TradeGroupEnum::METALS:
                    $title = 'Metals';
                    $trades = $this->getMetalOffers($user);
                    break;
                case TradeGroupEnum::DARK_THINGS:
                    $title = 'Umbral';
                    $trades = $this->getDarkThingsOffers($user);
                    break;
                case TradeGroupEnum::FOODS:
                    $title = 'Food';
                    $trades = $this->getFoodsOffers($user);
                    break;
                case TradeGroupEnum::CURIOSITIES:
                    $title = 'Curiosities';
                    $trades = $this->getCuriositiesOffers($user);
                    break;
                case TradeGroupEnum::PLUSHIES:
                    $title = 'Plushies';
                    $trades = $this->getPlushyOffers($user);
                    break;
                case TradeGroupEnum::GAMING:
                    $title = 'Portal';
                    $trades = $this->getGamingOffers();
                    break;
                case TradeGroupEnum::BOX_BOX:
                    $title = 'Box-box';
                    $trades = $this->getBoxBoxOffers();
                    break;
                default: throw new \Exception('You have unlocked trade group #' . $group . '... which does not exist. Ben should fix this.');
            }

            $offers[] = [
                'title' => $title,
                'trades' => $trades
            ];
        }
        return $offers;
    }

    private function getBoxBoxOffers(): array
    {
        return [
            new TraderOffer(
                TradeEnum::ID_BOX_BOX_FOR_RIDICULOUS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('This is Getting Ridiculous'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.'
            ),

            new TraderOffer(
                TradeEnum::ID_BOX_BOX_FOR_FLYING_BINDLE,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Flying Bindle'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.'
            ),

            new TraderOffer(
                TradeEnum::ID_BOX_BOX_FOR_GOLD_TRIFECTA,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Trifecta'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.'
            ),

            new TraderOffer(
                TradeEnum::ID_BOX_BOX_FOR_L33T_H4XX0R,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('l33t h4xx0r'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I honestly don\'t remember what exactly is in here. Memory of a fish, I suppose.'
            ),
        ];
    }

    private function getGamingOffers(): array
    {
        return [
            new TraderOffer(
                TradeEnum::ID_GLOWING_D4,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Sword'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Four-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And only four sides? That\'s just not right.'
            ),

            new TraderOffer(
                TradeEnum::ID_GLOWING_D6_A,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dragon Flag'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Six-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies.'
            ),

            new TraderOffer(
                TradeEnum::ID_GLOWING_D6_B,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sun Flag'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Six-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies.'
            ),

            new TraderOffer(
                TradeEnum::ID_GLOWING_D8,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glass Pendulum'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glowing Eight-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And _eight_ sides?? It\'s unnatural.'
            ),

            new TraderOffer(
                TradeEnum::ID_KEY_RING,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Key'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Key'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Key'), 1),
                    TraderOfferCostOrYield::createMoney(1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Key Ring'), 1) ],
                'Do enjoy!'
            )
        ];
    }

    private function getHolidayOffers(User $user)
    {
        $now = new \DateTimeImmutable();
        $dayOfWeek = $now->format('D');

        $offers = $this->getDayOfWeekTrades($dayOfWeek);

        if($this->calendarService->isEaster())
        {
            $blueEggItem = [
                'Chili Calamari', 'Deep-fried Toad Legs', 'Fisherman\'s Pie', 'Tomato Soup', 'Coffee Jelly'
            ][($user->getId() + (int)date('Y')) % 5];

            $yellowEggItem = [
                'Tinfoil Hat',
                'Unicorn Horn',
                'Candle',
            ][($user->getId() + (int)date('Y')) % 3];

            $offers[] = new TraderOffer(
                TradeEnum::ID_UPGRADE_BLUE_PLASTIC_EGGS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blue Plastic Egg'), 10)
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tofu'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rock'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName($blueEggItem), 1),
                ],
                'We fish collect the things, too, you know!'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_UPGRADE_YELLOW_PLASTIC_EGGS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Yellow Plastic Egg'), 5),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Cryptocurrency Wallet'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blunderbuss'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName($yellowEggItem), 1),
                ],
                'We fish collect the things, too, you know!'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_UPGRADE_PINK_PLASTIC_EGGS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Pink Plastic Egg'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Spirit Polymorph Potion'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Firestone'), 1),
                ],
                'We fish collect the things, too, you know!'
            );
        }

        if($now->format('M') === 'Oct')
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_UNICORN_HORN,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Talon'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Unicorn Horn'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... isosceles, though. (Isoscelic? Isoscelean? Whatever.)'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_TINFOIL_HAT,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Toadstool'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tea Leaves'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tinfoil Hat'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... crinkly, though. And no plume!'
            );
        }

        // talk like a pirate day
        if($this->calendarService->isTalkLikeAPirateDay())
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_RUSTY_RAPIER,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Scales'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Seaweed'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rusty Rapier'), 1),
                ],
                "If I had 10~~m~~ for every Rapier I found lying on the bottom of the ocean, I'd be a very wealthy fish!\n\nOhohohoho!"
            );
        }

        return $offers;
    }

    private function getMetalOffers(User $user): array
    {
        $offers = [
            new TraderOffer(
                TradeEnum::ID_GOLD_TO_SILVER_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1) ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_GOLD_TO_SILVER_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1) ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_IRON_TO_SILVER_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_IRON_TO_SILVER_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_SILVER_TO_IRON_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_SILVER_TO_IRON_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_SILVER_TO_GOLD_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Thank you kindly.'
            ),

            new TraderOffer(
                TradeEnum::ID_SILVER_TO_GOLD_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                ],
                'Thank you kindly.'
            ),
        ];

        return $offers;
    }

    private function getDarkThingsOffers(User $user): array
    {
        return [
            new TraderOffer(
                TradeEnum::ID_BLACKONITE,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Charcoal'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dark Matter'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackberries'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Tea'), 2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackonite'), 1) ],
                'The technique for forging Blackonite is unknown, even to Tell Samarzhoustia. But we _have_ established trade with a creature that knows the secret. The Charcoal, etc, is essentially an offering.'
            ),
            new TraderOffer(
                TradeEnum::ID_FAIRY_SWARM,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fairy Ring'), 3),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quinacridone Magenta Dye'), 1),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fairy Swarm'), 1) ],
                'Quinacridone Magenta is pretty valuable stuff. Even King Nebuludwigula XIII has but a few robes dyed that color.'
            )
        ];
    }

    private function getFoodsOffers(User $user): array
    {
        return [
            new TraderOffer(
                TradeEnum::ID_COOKING_BUDDY,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Cooking Buddy'), 1) ],
                'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!'
            ),
            new TraderOffer(
                TradeEnum::ID_BAG_FOR_PAINTED_ROD,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Painted Fishing Rod'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper Bag'), 1) ],
                'I just can\'t believe humans are allowed to carry a rod without a permit.'
            ),
            new TraderOffer(
                TradeEnum::ID_LIMESTONE_FOR_ROOTS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Limestone'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ginger'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Grandparoot'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Carrot'), 1),
                ],
                'Limestone is an important building material in Tell Samarzhoustia. We build beautiful palaces, and enormous chimera statues. Well, enormous by fish standards. You should visit, sometime.'
            ),

            new TraderOffer(
                TradeEnum::ID_GLASS_FOR_KETCHUP,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Glass'), 1 ) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sweet Beet'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tomato'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Vinegar'), 1),
                ],
                'There\'s a lot of Silica Grounds in Tell Samarzhoustia, of course, but turning them into Glass is an expensive process.'
            ),

            new TraderOffer(
                TradeEnum::ID_BEAN_MILK_FOR_PAPER,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bean Milk'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper'), 1) ],
                'We make something similar in Tell Samarzhoustia, but your Land Beans have a subtler flavor that\'s really starting to catch on.'
            ),

            new TraderOffer(
                TradeEnum::ID_TOFU_FOR_PAPER,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tofu'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper'), 3) ],
                'We make something similar in Tell Samarzhoustia, but your Land Beans have a subtler flavor that\'s really starting to catch on.'
            ),

            new TraderOffer(
                TradeEnum::ID_BLUE_CANDY_FOR_WITCH_HAZEL,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rock Candy'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Witch-hazel'), 1) ],
                'As you can imagine, Rock Candy doesn\'t last long in Tell Samarzhoustia. It has to be packaged - and consumed - very carefully. It\'s all a bit bougie, really. Not that I mind: trade\'s trade!'
            ),
        ];
    }

    private function getCuriositiesOffers(User $user): array
    {
        return [
            new TraderOffer(
                TradeEnum::ID_MONEY_SINK,
                [ TraderOfferCostOrYield::createMoney(1000) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Money Sink'), 1) ],
                'The Museum\'s curator insisted I make this offer...'
            ),

            new TraderOffer(
                TradeEnum::ID_GARBAGE_DISPOSAL,
                [ TraderOfferCostOrYield::createRecyclingPoints(1000) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Garbage Disposal'), 1) ],
                'The Museum\'s curator insisted I make this offer...'
            ),

            new TraderOffer(
                TradeEnum::ID_LEVEL_2_SWORD,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Secret Seashell'), 20) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Level 2 Sword'), 1) ],
                'It\'s dangerous to go alone. Take this.'
            ),

            new TraderOffer(
                TradeEnum::ID_3D_PRINTER,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Baabble'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('3D Printer'), 1) ],
                'Please use this responsibly, human. The amount of Plastic ending up in the oceans these days is a bit troubling.'
            ),

            new TraderOffer(
                TradeEnum::ID_MUSICAL_SCALES,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Music Note'), 7) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Musical Scales'), 1) ],
                'I don\'t mind letting you in on a little Tell Samarzhoustian secret: you can do this "trade" yourself at home. Just combine 7 Music Notes.'
            ),

            new TraderOffer(
                TradeEnum::ID_RINGS_ON_STRINGS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ring'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Planetary Ring'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('String'), 1)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rings on Strings'), 1) ],
                'This item feels kind of silly, doesn\'t it? Well, I suppose it\'s not for me to say.'
            ),
        ];
    }

    private function getPlushyOffers(User $user)
    {
        return [
            new TraderOffer(
                TradeEnum::ID_PEACOCK_TO_FLUFF_HEART,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Peacock Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...'
            ),

            new TraderOffer(
                TradeEnum::ID_BULBUN_TO_FLUFF_HEART,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bulbun Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...'
            ),

            new TraderOffer(
                TradeEnum::ID_SNEQO_TO_FLUFF_HEART,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sneqo Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...'
            ),

            new TraderOffer(
                TradeEnum::ID_RAINBOW_DOLPHIN_TO_FLUFF_HEART,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rainbow Dolphin Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1) ],
                'These things are so cute...'
            ),

            new TraderOffer(
                TradeEnum::ID_FLUFF_HEART_TO_PEACOCK,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Peacock Plushy'), 1) ],
                'Such a classic.'
            ),

            new TraderOffer(
                TradeEnum::ID_FLUFF_HEART_TO_BULBUN,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bulbun Plushy'), 1) ],
                'These make great body pillows. Well, I guess it is a bit small for you, though.'
            ),

            new TraderOffer(
                TradeEnum::ID_FLUFF_HEART_TO_SNEQO,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sneqo Plushy'), 1) ],
                'I don\'t know why these things are plushies. They have arms! Snakes shouldn\'t have arms!'
            ),

            new TraderOffer(
                TradeEnum::ID_FLUFF_HEART_TO_RAINBOW_DOLPHIN,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(12),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rainbow Dolphin Plushy'), 1) ],
                'The extra 2~~m~~ covers a tax imposed by the Great Rainbow Dolphin Empire.'
            ),
        ];
    }

    private function getDayOfWeekTrades(string $dayOfWeek): array
    {
        $offers = [];

        $leapDay = $this->calendarService->isLeapDay();

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_QUINT_FOR_MOON_PEARL,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wings'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Benjamin Franklin'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                'I don\'t know where all the Monday hate comes from. Mondays are _great_. You get Moon Pearls on Mondays!'
            );
        }

        if($dayOfWeek === 'Tue' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_SELL_LASER_GUIDED_SWORD,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Laser-guided Sword'), 1) ],
                [ TraderOfferCostOrYield::createMoney(50) ],
                'Did you know "Tuesday" is named for a god of combat, and justice?'
            );
        }

        if($dayOfWeek === 'Wed' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_QUINT_FOR_FEATHERS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Feathers'), 3),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1) ],
                'What\'s the theme of today\'s trade? I\'ll never tell!'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_FEATHERS_FOR_QUINT,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Feathers'), 3) ],
                'What\'s the theme of today\'s trade? I\'ll never tell!'
            );
        }

        if($dayOfWeek === 'Thu' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_TRIDENT_FOR_BAG_OF_BEANS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ceremonial Trident'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bag of Beans'), 1) ],
                $dayOfWeek === 'Thu' ? 'If it were up to me, today would be called Poseidon\'s Day, but it wasn\'t, so fine: Thor\'s Day it is, I guess.' : 'It\'s like all the rules go out the window on leap days!'
            );
        }

        if($dayOfWeek === 'Fri' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_NOT_RED_GOLD_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wheat Flower'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Sorry: we couldn\'t get out flippers on actual Red Gold.'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_NOT_RED_GOLD_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rice Flower'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Sorry: we couldn\'t get out flippers on actual Red Gold.'
            );
        }

        if($dayOfWeek === 'Sat' || $leapDay)
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_GET_YOGURT,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Flute'), 1) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Aging Powder'), 1)
                ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. They also told me to tell anyone who made this trade that you can combine Plain Yogurt, Creamy Milk, and Aging Powder to make _more_ Plain Yogurt. I told them that that\'s basically common knowledge at this point, but they insisted, so... yeah. There you go.'
            );

            $offers[] = new TraderOffer(
                TradeEnum::ID_SELL_YOGURT,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 2) ],
                [ TraderOfferCostOrYield::createMoney(5) ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. I guess they like yogurt?'
            );
        }

        if($dayOfWeek === 'Sun')
        {
            $offers[] = new TraderOffer(
                TradeEnum::ID_GREENHOUSE_DEED,
                [ TraderOfferCostOrYield::createMoney(100) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Deed for Greenhouse Plot'), 1) ],
                "Oh, fun, a greenhouse! What kind of Kelp will you be gr-- oh. Right, I suppose you'll just be growing Landweed, and such.\n\nWell.\n\nHave fun with that, I suppose."
            );

            $offers[] = new TraderOffer(
                'sunflower1',
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Triangle'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!'
            );

            $offers[] = new TraderOffer(
                'sunflower2',
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Key'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!'
            );

            $offers[] = new TraderOffer(
                'sunflower3',
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Tuning Fork'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Ah: you doing some Beehive stuff? Or making one of those Night and Day swords? Well, have a happy Sunday, regardless!'
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
                        throw new \InvalidArgumentException('You do not have the items needed to make this exchange. (Expected ' . $cost['quantity'] . ' items; only found ' . $quantity . '.)');

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($user->getMoneys() < $cost->quantity)
                        throw new \InvalidArgumentException('You do not have the moneys needed to make this exchange.');

                    if(mt_rand(1, 50) === 1)
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
                        $this->inventoryService->receiveItem($yield->item, $user, null, $itemDescription, LocationEnum::HOME);

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if(mt_rand(1, 50) === 1)
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
        $h1 = mt_rand(0, 255);
        $h2 = mt_rand(0, 255);
        $h3 = mt_rand(0, 255);

        $l2 = mt_rand(mt_rand(40, 120), 150);

        $s3 = mt_rand(mt_rand(0, 40), mt_rand(160, 255));
        $l3 = mt_rand(mt_rand(0, 40), mt_rand(160, 255));

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
            ->setColorA(ColorFunctions::HSL2Hex($h1 / 256, mt_rand(56, 100) / 100, 0.46))
            ->setColorB(ColorFunctions::HSL2Hex($h2 / 256, mt_rand(56, 100) / 100, $l2 / 255))
            ->setColorC(ColorFunctions::HSL2Hex($h3 / 256, $s3 / 255, $l3 / 255))
        ;
    }

    function generateTrader(): Trader
    {
        $trader = (new Trader())
            ->setName(ArrayFunctions::pick_one(self::TRADER_NAMES))
        ;

        $this->recolorTrader($trader);

        return $trader;
    }
}
