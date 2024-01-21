<?php
namespace App\Service;

use App\Entity\DailyMarketItemAverage;
use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\Trader;
use App\Entity\TradesUnlocked;
use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\TradeGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\CalendarFunctions;
use App\Functions\ColorFunctions;
use App\Functions\ItemRepository;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly TransactionService $transactionService,
        private readonly IRandom $rng,
        private readonly InventoryRepository $inventoryRepository,
        private readonly Clock $clock,
        private readonly EntityManagerInterface $em,
        private readonly CacheHelper $cache
    )
    {
    }

    /**
     * @return int[]
     */
    public function getUnlockedTradeGroups(User $user): array
    {
        $tradesUnlocked = $this->em->getRepository(TradesUnlocked::class)->findBy([
            'user' => $user->getId()
        ]);

        return array_map(fn(TradesUnlocked $tu) => $tu->getTrades(), $tradesUnlocked);
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
            $exchange = ArrayFunctions::find_one($offerGroup['trades'], fn(TraderOffer $o) => $o->id === $id);

            if($exchange !== null)
                return $exchange;
        }

        return null;
    }

    public function getOffers(User $user): array
    {
        $quantities = $this->inventoryRepository->getInventoryQuantities($user, LocationEnum::HOME, 'name');

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
                    $trades = $this->getUmbralThingsOffers($user, $quantities);
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
                case TradeGroupEnum::BLEACH:
                    $title = 'Bleach';
                    $trades = $this->getBleachOffers($user, $quantities);
                    break;
                case TradeGroupEnum::DIGITAL:
                    $title = 'Digital';
                    $trades = $this->getDigitalOffers($user, $quantities);
                    break;
                case TradeGroupEnum::BUGS:
                    $title = 'Bugs';
                    $trades = $this->getBugOffers($user, $quantities);
                    break;
                case 3: // old "FOODS" group unlock
                case 5: // old "BOX-BOX" group unlock
                    continue 2; // why "2"? see https://www.php.net/manual/en/control-structures.continue.php >_>
                default:
                    throw new \Exception('You have unlocked trade group #' . $group . '... which does not exist. Ben should fix this.');
            }

            $offers[] = [
                'title' => $title,
                'trades' => $trades
            ];
        }

        usort($offers, fn($a, $b) => $a['title'] <=> $b['title']);

        $holidayOffers = $this->getSpecialOffers($user, $quantities);

        if(count($holidayOffers) > 0)
        {
            array_unshift($offers, [
                'title' => 'Special',
                'trades' => $holidayOffers
            ]);
        }

        return $offers;
    }

    private function getBleachOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Dragon Flag'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Flag'), 1) ],
                'Not the flag design you were looking for? You should try the Flag of Tell Samarzhoustia!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Sun Flag'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Flag'), 1) ],
                'Not the flag design you were looking for? You should try the Flag of Tell Samarzhoustia!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Black Flag'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Flag'), 1) ],
                'Not the flag design you were looking for? You should try the Flag of Tell Samarzhoustia!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Black Feathers'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Feathers'), 1) ],
                'Sometimes it\'s just easier to defeat a demon than a pegasus, you know?',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Filthy Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(5)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Cloth'), 1) ],
                'There you go! Good as new!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Chocolate-stained Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(5)
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Cloth'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Cocoa Powder'), 1),
                ],
                'There you go! Good as new! And I even kept the Cocoa Powder for you.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Chocolate Feather Bonnet'), 1),
                    TraderOfferCostOrYield::createMoney(100)
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Chocolate Feather Bonnet'), 1),
                ],
                "It's still edible, too! Oh, but did you know there's a bee in there? I think it was a little agitated by the bleaching process, so... eat carefully, I guess is what I'm saying.",
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Black Baabble'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(25),
                    TraderOfferCostOrYield::createMoney(25),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Baabble'), 1) ],
                'Just don\'t tell the satyrs I did this for you. I\'m pretty sure they\'d consider it cheating...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Red Firework'), 1),
                    TraderOfferCostOrYield::createMoney(10)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Firework'), 1) ],
                'There\'s a little Tell Samarzhoustia chemistry for you!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Blue Firework'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Firework'), 1) ],
                'There\'s a little Tell Samarzhoustia chemistry for you!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Top Hat'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Bright Top Hat'), 1) ],
                'Huh: the band bleached out to be purple? Must be some kind of powerful dye they used, there...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Jolliest Roger'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Creamiest Roger'), 1) ],
                'Well, it came out kind of funny. I think I liked the red better, myself, but hey: whatever floats your pirate boat!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Dark Horsey Hat'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Zebra "Horsey" Hat'), 1) ],
                'Someone was telling me that zebras _aren\'t_ a kind of horse? Is that true?? I can\'t keep all your weird land animals straight.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Zebra "Horsey" Hat'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Horsey Hat'), 1) ],
                'And you\'re _super_ sure zebras aren\'t horses? It\'s just-- I\'m not-- ... you know??',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Horsey Hat'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Horsey Hat'), 1) ],
                'It\'s fun that horses come in so many colors! But what about, like, purple horses? Are there any of those?',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Paint Stripper'), 1) ],
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
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'NUL'), 5),
                    TraderOfferCostOrYield::createMoney(2),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 1) ],
                'XORs are, like, one of the de facto currencies for those trading in Project-E.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Pointer'), 3),
                    TraderOfferCostOrYield::createMoney(1),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 1) ],
                'XORs are, like, one of the de facto currencies for those trading in Project-E.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fruits & Veggies Box'), 1) ],
                'Enjoy the fruits and veggies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Baker\'s Box'), 1) ],
                'Enjoy the baking supplies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Handicrafts Supply Box'), 1) ],
                'Enjoy the crafting supplies!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'XOR'), 5) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hat Box'), 1) ],
                'Enjoy the hat!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Short-range Telephone'), 1),
                    TraderOfferCostOrYield::createMoney(100),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Rotary Phone'), 1) ],
                "Enjoy the hat!\n\nIt _is_ a hat, right??",
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createRecyclingPoints(200)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Digital Camera'), 1) ],
                "An aspiring photographer, eh? Have fun!",
                $user,
                $quantities
            ),
        ];
    }

    private function getBugOffers(User $user, array $quantities): array
    {
        $stickInsect = ItemRepository::findOneByName($this->em, 'Stick Insect');

        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 1), TraderOfferCostOrYield::createRecyclingPoints(1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Yeast'), 3) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 1), TraderOfferCostOrYield::createRecyclingPoints(1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Sunflower'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 3), TraderOfferCostOrYield::createRecyclingPoints(3) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Quintessence'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 6), TraderOfferCostOrYield::createRecyclingPoints(6) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Wrapped Sword'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 10), TraderOfferCostOrYield::createRecyclingPoints(10) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fish Statue'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 14), TraderOfferCostOrYield::createRecyclingPoints(14) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hat Box'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 18), TraderOfferCostOrYield::createRecyclingPoints(15) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Chest'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem($stickInsect, 20), TraderOfferCostOrYield::createRecyclingPoints(20) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Ruby Chest'), 1) ],
                'Useful little fellas, these Stick Insects! Thanks a lot!',
                $user,
                $quantities
            ),
        ];
    }

    private function getGamingOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(100) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hollow Earth Booster Pack: Beginnings'), 1) ],
                'Have fun!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(100) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hollow Earth Booster Pack: Community Pack'), 1) ],
                'Have fun!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(4) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Glowing Four-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And only four sides? That\'s just not right.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(6) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Glowing Six-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(8) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Glowing Eight-sided Die'), 1) ],
                'To be honest, those dice kind of give me the willies. And _eight_ sides?? It\'s unnatural.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createRecyclingPoints(20),
                    TraderOfferCostOrYield::createMoney(40)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Scroll of Dice'), 1) ],
                'To be honest, those dice kind of give me the willies. A whole scroll of them? Yeah, no thanks!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Iron Key'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Key'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Key'), 1),
                    TraderOfferCostOrYield::createMoney(1),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Key Ring'), 1) ],
                'Do enjoy!',
                $user,
                $quantities
            ),
        ];
    }

    private function getSpecialOfferItemsAndPrices(int $offers): array
    {
        if($offers <= 0) return [];

        return $this->cache->getOrCompute(
            'Trader Special Offers ' . $this->clock->now->format('Y-m-d'),
            \DateInterval::createFromDateString('1 day'),
            fn() => $this->computeSpecialOfferItemsAndPrices($offers)
        );
    }

    private function computeSpecialOfferItemsAndPrices(int $offers)
    {
        $results = [];

        $uniqueOfferItems = $this->findItemsForDailySpecialOffers($offers);

        foreach($uniqueOfferItems as $uniqueOfferItem)
        {
            $averageValue = $this->em->getRepository(DailyMarketItemAverage::class)->createQueryBuilder('a')
                ->select('AVG(a.averagePrice) AS averagePrice, COUNT(a.id) AS count')
                ->andWhere('a.item=:item')->setParameter('item', $uniqueOfferItem->getId())
                ->andWhere('a.date >= :date')->setParameter('date', $this->clock->now->modify('-3 months'))
                ->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

            $computedValue = $uniqueOfferItem->getRecycleValue() * 1.3334 + $uniqueOfferItem->getMuseumPoints() * 0.5;

            $value = (int)($averageValue['count'] < 28 == 0 ? $computedValue : ceil(($averageValue['averagePrice'] + $computedValue) / 2));

            $results[] = [
                'item' => $uniqueOfferItem,
                'value' => $value
            ];
        }

        return $results;
    }

    /**
     * @return TraderOffer[]
     */
    private function getSpecialOffers(User $user, array $quantities): array
    {
        $offers = [];

        if(CalendarFunctions::isPsyPetsBirthday($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(20),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, '"Roy" Plushy'), 1) ],
                'This is a special offer for PsyPets\' birthday. Tess and Mia insisted. Oh, but cool bonus: unlike other plushies, you can wear this as a hat. Not sure why that is, really...',
                $user,
                $quantities
            );

        }

        if(CalendarFunctions::isValentinesOrAdjacent($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Twu Wuv'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'White Cloth'), 1),
                    TraderOfferCostOrYield::createMoney(100),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Pink Bow'), 1),
                ],
                'Please enjoy the complimentary chocolate, and remember: all candies "recycled" during Valentine\'s are guaranteed to find their way to the Giving Tree, where any pet may collect them!',
                $user,
                $quantities,
                true
            );
        }

        if(CalendarFunctions::isStockingStuffingSeason($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Talon'), 2) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Antlers'), 1) ],
                'These were cut from Antlerfish earlier this year. Catch and release, of course. The antlers grow back every year, mating season is already over, and the antlers actually make the fish easier targets for predators, so the system really works out for everyone! Well, except for Antlerfish predators, I suppose. They kinda\' lose out.',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(20) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Tawny Ears'), 1) ],
                'I _guess_ these could be reindeer ears? That\'s my best guess, anyway. Oh: made of plastic and fluff, of course! They\'re not _real_ ears! That would be brutal.',
                $user,
                $quantities
            );
        }

        if(CalendarFunctions::isEaster($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Blue Plastic Egg'), 5)
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Chili Calamari'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Deep-fried Toad Legs'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fisherman\'s Pie'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Tomato Soup'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Coffee Jelly'), 1),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Yellow Plastic Egg'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Spice Rack'), 3),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Pink Plastic Egg'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hat Box'), 1),
                ],
                'We fish collect the things, too, you know!',
                $user,
                $quantities
            );
        }

        if($this->clock->now->format('M') === 'Nov')
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Turkey King'), 1)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Bleached Turkey Head'), 1) ],
                'Oh, I don\'t need any moneys. The little crown is good enough for me.',
                $user,
                $quantities
            );
        }

        if($this->clock->now->format('M') === 'Oct')
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Talon'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Quintessence'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Unicorn Horn'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... isosceles, though. (Isoscelic? Isoscelean? Whatever.)',
                $user,
                $quantities
            );

            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Toadstool'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Tinfoil Hat'), 1),
                ],
                'Triangular hats are where it\'s at. That one\'s rather... crinkly, though. And no plume!',
                $user,
                $quantities
            );
        }

        // talk like a pirate day
        if(CalendarFunctions::isTalkLikeAPirateDay($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Rusty Rapier'), 1),
                ],
                "If I had 10~~m~~ for every Rapier I found lying on the bottom of the ocean, I'd be a very wealthy fish!\n\nOhohohoho!",
                $user,
                $quantities
            );
        }

        if(CalendarFunctions::isMayThe4th($this->clock->now))
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Photon'), 1),
                    TraderOfferCostOrYield::createMoney(100),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Lightpike'), 1),
                ],
                "Masters of the Lightpike can cut you down in one stab!",
                $user,
                $quantities
            );
        }

        $uniqueOffers = $this->getSpecialOfferItemsAndPrices(self::NUMBER_OF_DAILY_SPECIAL_OFFERS - count($offers));

        foreach($uniqueOffers as $uniqueOffer)
        {
            $offers[] = TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem($uniqueOffer['item'], 1),
                ],
                [
                    TraderOfferCostOrYield::createMoney($uniqueOffer['value']),
                ],
                'It\'s a special offer, just for you.',
                $user,
                $quantities
            );
        }

        return $offers;
    }

    public const NUMBER_OF_DAILY_SPECIAL_OFFERS = 5;

    /**
     * @return Item[]
     */
    public function findItemsForDailySpecialOffers(int $offers): array
    {
        $count = $this->em->getRepository(Item::class)->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->andWhere('i.recycleValue > 0')
            ->andWhere('i.treasure IS NULL')
            ->andWhere('i.fuel < 4')
            ->andWhere('i.fertilizer < 4')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $random = (($this->clock->now->format('Ymd') - 20040404) * 6737 + 76801) % ($count - $offers + 1);

        return $this->em->getRepository(Item::class)->createQueryBuilder('i')
            ->andWhere('i.recycleValue > 0')
            ->andWhere('i.treasure IS NULL')
            ->andWhere('i.fuel < 4')
            ->andWhere('i.fertilizer < 4')
            ->orderBy('i.id', 'ASC')
            ->setFirstResult($random)
            ->setMaxResults($offers)
            ->getQuery()
            ->execute()
        ;
    }

    private function getMetalOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Bar'), 1) ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Ore'), 1) ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Iron Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Iron Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Iron Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Iron Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Bar'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(3),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Bar'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Silver Ore'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Ore'), 1),
                ],
                'Thank you kindly.',
                $user,
                $quantities
            ),
        ];
    }

    private function getUmbralThingsOffers(User $user, array $quantities): array
    {
        return [
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Charcoal'), 2),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Dark Matter'), 2),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Blackberries'), 2),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Black Tea'), 2),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Blackonite'), 1) ],
                'The technique for forging Blackonite is unknown, even to Tell Samarzhoustia. But we _have_ established trade with a creature that knows the secret. The Charcoal, etc, is essentially an offering.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fairy Ring'), 3),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Quinacridone Magenta Dye'), 1),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fairy Swarm'), 1) ],
                'Quinacridone Magenta is pretty valuable stuff. Naturally, King Nebuludwigula XIII has a few robes dyed that color.',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Feathers'), 3),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Quintessence'), 1) ],
                'In Tell Samarzhoustian mythology, birds are associated with magic...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Quintessence'), 1),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Feathers'), 3) ],
                'In Tell Samarzhoustian mythology, birds are associated with magic...',
                $user,
                $quantities
            ),
            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Heart Beetle'), 1),
                    TraderOfferCostOrYield::createRecyclingPoints(2),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Harmony-dusted Donut'), 1) ],
                'What? No, we don\'t _cook_ them! That would be barbaric...',
                $user,
                $quantities
            )
        ];
    }

    private function stillOfferingDeedForGreenhousePlot(User $user): bool
    {
        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return true;

        $deedForGreenhousePlot = ItemRepository::findOneByName($this->em, 'Deed for Greenhouse Plot');

        // keep offering them for sale until you've donated one to the museum
        if($this->em->getRepository(MuseumItem::class)->count([ 'user' => $user, 'item' => $deedForGreenhousePlot ]) == 0)
            return true;

        return false;
    }

    private function getFoodsOffers(User $user, array $quantities): array
    {
        $offers = [];

        if($this->stillOfferingDeedForGreenhousePlot($user))
        {
            $deedForGreenhousePlot = ItemRepository::findOneByName($this->em, 'Deed for Greenhouse Plot');

            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createMoney(100) ],
                [ TraderOfferCostOrYield::createItem($deedForGreenhousePlot, 1) ],
                "Oh, fun, a greenhouse! What kind of Kelp will you be gr-- oh. Right, I suppose you'll just be growing Landweed, and such.\n\nWell.\n\nHave fun with that, I suppose.",
                $user,
                $quantities,
                true
            );
            $offers[] = TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(50) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Deed for Greenhouse Plot'), 1) ],
                "Oh, fun, a greenhouse! What kind of Kelp will you be gr-- oh. Right, I suppose you'll just be growing Landweed, and such.\n\nWell.\n\nHave fun with that, I suppose.",
                $user,
                $quantities,
                true
            );
        }

        $offers[] = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Limestone'), 2),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Scroll of Tell Samarzhoustian Delights'), 1),
            ],
            'Limestone is an important building material in Tell Samarzhoustia. We build beautiful palaces, and enormous chimera statues. Well, enormous by fish standards. You should visit, sometime.',
            $user,
            $quantities
        );

        $offers[] = TraderOffer::createTradeOffer(
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Moon Pearl'), 1) ],
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Cooking Buddy'), 1) ],
            'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!',
            $user,
            $quantities
        );

        $offers[] = TraderOffer::createTradeOffer(
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Moon Pearl'), 1) ],
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Hot Pot'), 1) ],
            'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!',
            $user,
            $quantities
        );

        $offers[] = TraderOffer::createTradeOffer(
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Moon Pearl'), 1) ],
            [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Composter'), 1) ],
            'That\'s no knock-off! Tell Samarzhoustia trades directly with the Eridanus Federation!',
            $user,
            $quantities
        );

        return $offers;
    }

    private function getCuriositiesOffers(User $user, array $quantities): array
    {
        $moonName = $this->rng->rngNextFromArray([
            'Europa', 'Ganymede', 'Callisto', 'Mimas', 'Enceladus', 'Titan', 'Miranda', 'Umbriel', 'Triton'
        ]);

        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Secret Seashell'), 20) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Level 2 Sword'), 1) ],
                'It\'s dangerous to go alone. Take this.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Black Baabble'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, '3D Printer'), 1) ],
                'Please use this responsibly, human. The amount of Plastic ending up in the oceans these days is a bit troubling.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createMoney(400),
                    TraderOfferCostOrYield::createRecyclingPoints(200),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Submarine'), 1) ],
                'Derelict submarines sometimes drift into Tell Samarzhoustia. We fix \'em up, and sell them to land-dwellers such as yourself! Have fun!',
                $user,
                $quantities,
                true
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Music Note'), 7) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Musical Scales'), 1) ],
                'I don\'t mind letting you in on a little Tell Samarzhoustian secret: you can do this "trade" yourself at home. Just combine 7 Music Notes.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Gold Ring'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Planetary Ring'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Onion Rings'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'String'), 1)
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Rings on Strings'), 1) ],
                'This item feels kind of silly, doesn\'t it? Well, I suppose it\'s not for me to say. Oh, and thanks for the Onion Rings. I was feeling a little peckish.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Money Sink'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Garbage Disposal'), 1),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Li\'l Pocket Dimension'), 1) ],
                'I happened to get a few of these while traveling with a... well, let\'s just say he\'s a traveler. And, I mean, he didn\'t tell me _not_ to sell them, so... here we are.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Nón Lá'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Toadstool'), 1),
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Money Sink'), 1),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Nấm Lá'), 1) ],
                'The goblin shark that sold me this told me that "nấm" mean "mushroom". Seems kind of on-the-nose, to me, but that\'s goblin sharks for you.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createMoney(1000) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Money Sink'), 1) ],
                'The Museum\'s curator insisted I make this offer...',
                $user,
                $quantities,
                true
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createRecyclingPoints(1000) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Garbage Disposal'), 1) ],
                'The Museum\'s curator insisted I make this offer...',
                $user,
                $quantities,
                true
            ),
        ];
    }

    private function getPlushyOffers(User $user, array $quantities)
    {
        return [
            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Peacock Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Bulbun Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Sneqo Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Rainbow Dolphin Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Phoenix Plushy'), 1) ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1) ],
                'These things are so cute...',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Peacock Plushy'), 1) ],
                'Such a classic.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Bulbun Plushy'), 1) ],
                'These make great body pillows. Well, I guess it is a bit small for you, though.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Sneqo Plushy'), 1) ],
                'I don\'t know why these things are plushies. They have arms! Snakes shouldn\'t have arms!',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(12),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Rainbow Dolphin Plushy'), 1) ],
                'The extra 2~~m~~ covers a tax imposed by the Great Rainbow Dolphin Empire.',
                $user,
                $quantities
            ),

            TraderOffer::createTradeOffer(
                [
                    TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Fluff Heart'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [ TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($this->em, 'Phoenix Plushy'), 1) ],
                'A rare and beautiful bird, for a rare and beautiful customer!',
                $user,
                $quantities
            ),
        ];
    }

    public function userCanMakeExchange(User $user, TraderOffer $exchange, int $location): bool
    {
        foreach($exchange->cost as $cost)
        {
            switch($cost->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    $quantity = InventoryService::countInventory($this->em, $user->getId(), $cost->item->getId(), $location);

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
                    throw new \Exception('Unexpected cost type "' . $cost->type . '"!? Weird! Ben should fix this!');
            }
        }

        return true;
    }

    /**
     * CAREFUL: Also used by some items, to perform transmutations.
     */
    public function makeExchange(User $user, TraderOffer $exchange, int $location, int $quantity, string $itemDescription = 'Received by trading with the Trader.')
    {
        foreach($exchange->cost as $cost)
        {
            switch($cost->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    $itemQuantity = $this->inventoryService->loseItem($user, $cost->item->getId(), $location, $cost->quantity * $quantity);

                    if($itemQuantity < $cost->quantity * $quantity)
                        throw new PSPNotFoundException('You do not have the items needed to make this exchange. (Expected ' . ($cost->quantity * $quantity) . ' items; only found ' . $itemQuantity . '.)');

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($user->getMoneys() < $cost->quantity * $quantity)
                        throw new PSPNotEnoughCurrencyException($cost->quantity * $quantity . '~~m~~', $user->getMoneys() . '~~m~~');

                    if($this->rng->rngNextInt(1, 50) === 1)
                        $this->transactionService->spendMoney($user, $cost->quantity * $quantity, 'Traded away at the Trader. (That\'s usually just called "buying", right?)', true, [ 'Trader' ]);
                    else
                        $this->transactionService->spendMoney($user, $cost->quantity * $quantity, 'Traded away at the Trader.', true, [ 'Trader' ]);

                    break;

                case CostOrYieldTypeEnum::RECYCLING_POINTS:
                    if($user->getRecyclePoints() < $cost->quantity * $quantity)
                        throw new PSPNotEnoughCurrencyException($cost->quantity * $quantity . '♺', $user->getRecyclePoints() . '♺');

                    $this->transactionService->spendRecyclingPoints($user, $cost->quantity * $quantity, 'Traded away at the Trader.', [ 'Trader' ]);

                    break;

                default:
                    throw new \Exception('Unexpected cost type "' . $cost->type . '"!? Weird! Ben should fix this!');
            }
        }

        foreach($exchange->yield as $yield)
        {
            switch($yield->type)
            {
                case CostOrYieldTypeEnum::ITEM:
                    for($i = 0; $i < $yield->quantity * $quantity; $i++)
                        $this->inventoryService->receiveItem($yield->item, $user, null, $itemDescription, $location, $exchange->lockedToAccount);

                    break;

                case CostOrYieldTypeEnum::MONEY:
                    if($this->rng->rngNextInt(1, 50) === 1)
                        $this->transactionService->getMoney($user, $yield->quantity * $quantity, 'Traded for at the Trader. (That\'s usually just called "selling", right?)', [ 'Trader' ]);
                    else
                        $this->transactionService->getMoney($user, $yield->quantity * $quantity, 'Traded for at the Trader.', [ 'Trader' ]);

                    break;

                case CostOrYieldTypeEnum::RECYCLING_POINTS:
                    $this->transactionService->getRecyclingPoints($user, $yield->quantity * $quantity, 'Traded for at the Trader.', [ 'Trader' ]);
                    break;

                default:
                    throw new \Exception('Unexpected yield type "' . $yield->type . '"!? Weird! Ben should fix this!');
            }
        }
    }

    public static function recolorTrader(IRandom $rng, Trader $trader)
    {
        $h1 = $rng->rngNextInt(0, 255);
        $h2 = $rng->rngNextInt(0, 255);
        $h3 = $rng->rngNextInt(0, 255);

        $l2 = $rng->rngNextInt($rng->rngNextInt(40, 120), 150);

        $s3 = $rng->rngNextInt($rng->rngNextInt(0, 40), $rng->rngNextInt(160, 255));
        $l3 = $rng->rngNextInt($rng->rngNextInt(0, 40), $rng->rngNextInt(160, 255));

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
            ->setColorA(ColorFunctions::HSL2Hex($h1 / 256, $rng->rngNextInt(56, 100) / 100, 0.46))
            ->setColorB(ColorFunctions::HSL2Hex($h2 / 256, $rng->rngNextInt(56, 100) / 100, $l2 / 255))
            ->setColorC(ColorFunctions::HSL2Hex($h3 / 256, $s3 / 255, $l3 / 255))
        ;
    }

    public static function generateTrader(IRandom $rng): Trader
    {
        $trader = (new Trader())
            ->setName($rng->rngNextFromArray(self::TRADER_NAMES))
        ;

        self::recolorTrader($rng, $trader);

        return $trader;
    }
}
