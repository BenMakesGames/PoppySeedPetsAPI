<?php
namespace App\Service;
use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class TraderService
{
    private const ID_PROOF_OF_ADVENTURING = 'proofOfAdventuring';
    private const ID_LEVEL_2_SWORD = 'level2Sword';
    private const ID_RUSTY_RAPIER = 'rustyRapier';
    private const ID_GREENHOUSE_DEED = 'greenhouseDeed';
    private const ID_COOKING_BUDDY = 'cookingBuddy';
    private const ID_MOON_PEARL_FOR_10_MONS = 'monday10Mons';
    private const ID_WOODEN_SWORD_FOR_5_MONS = 'tuesday5Mons';
    private const ID_RAPIER_FOR_10_MONS = 'rapierFor10Mons';
    private const ID_COMPILER_FOR_BAG_OF_BEANS = 'bagOfBeans';
    private const ID_QUINT_FOR_FEATHERS = 'quintForFeathers';
    private const ID_COMPILER_FOR_10_MONS = 'compilerFor10Mons';
    private const ID_GET_YOGURT = 'buyYogurt';
    private const ID_SELL_YOGURT = 'sellYogurt';
    private const ID_NOT_RED_GOLD_1 = 'notRedGold1';
    private const ID_NOT_RED_GOLD_2 = 'notRedGold2';
    private const ID_FOR_SWEET_BEET = 'forSweetBeet';
    private const ID_MUSICAL_SCALES = 'musicalScales';
    private const ID_FISH_FOR_PAPER = 'fishForPaper';
    private const ID_BLACKONITE = 'blackonite';
    private const ID_LIMESTONE_FOR_ROOTS = 'limestoneForRoots';
    private const ID_TOMATO_FOR_WHITE_CLOTH = 'tomatoForWhiteCloth';
    private const ID_BAG_FOR_PAINTED_ROD = 'bagForPaintedRod';
    private const ID_3D_PRINTER = '3DPrinterPlz';
    private const ID_BLUE_CANDY_FOR_WITCH_HAZEL = 'blueCandyForWitchHazel';
    private const ID_GOLD_TO_SILVER_1 = 'goldToSilver1';
    private const ID_GOLD_TO_SILVER_2 = 'goldToSilver2';
    private const ID_IRON_TO_SILVER_1 = 'ironToSilver1';
    private const ID_IRON_TO_SILVER_2 = 'ironToSilver2';
    private const ID_SILVER_TO_IRON_1 = 'silverToIron1';
    private const ID_SILVER_TO_IRON_2 = 'silverToIron2';
    private const ID_SILVER_TO_GOLD_1 = 'silverToGold1';
    private const ID_SILVER_TO_GOLD_2 = 'silverToGold2';
    private const ID_BOX_BOX_FOR_RIDICULOUS = 'boxBox';

    private $itemRepository;
    private $inventoryService;
    private $userStatsRepository;

    public function __construct(
        ItemRepository $itemRepository, InventoryService $inventoryService, UserStatsRepository $userStatsRepository
    )
    {
        $this->itemRepository = $itemRepository;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function getOffers(User $user)
    {
        $now = new \DateTimeImmutable();

        $possibleDialog = [
            'My offerings change daily.',
            'Don\'t see anything you like? Check back tomorrow.',
            'Different day, different deals!',
            'Whoa, it\'s ' . $now->format('l') . ' already? It totally doesn\'t feel like a ' . $now->format('l') . ', you know?',
        ];

        $dialog = $possibleDialog[$user->getDailySeed() % count($possibleDialog)];

        /** @var TraderOffer[] $offers */
        $offers = [];

        $date = $now->format('M j');
        $dayOfWeek = $now->format('D');
        $dayOfTheYear = (int)$now->format('z') + $user->getDailySeed();

        $leapDay = $date === 'Feb 29';

        if($date === 'Oct 31' || $date === 'Oct 30' || $date === 'Oct 29' || $date === 'Oct 28')
        {
            if($date === 'Oct 31')
                $dialog = "Halloweeeeeeeeeeeeeeee\n\neeeeeeeeeeeeeeeeee\n\neeen!!!\n\n\n\nHalloween.";
            else
                $dialog = 'Halloween\'s coming up! Don\'t forget!';

            // TODO: ways to get candy, and/or other halloween-y things
        }

        if($date === 'Oct 31' || $date === 'Nov 1' || $date === 'Nov 2' || $date === 'Nov 3')
        {
            if($date !== 'Oct 31')
                $dialog = 'Did you have a fun halloween?';

            // TODO: trade halloween rewards for other things
        }

        // talk like a pirate day
        if($date === 'Sep 19')
        {
            $offers[] = new TraderOffer(
                self::ID_RUSTY_RAPIER,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Scales'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Seaweed'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rusty Rapier'), 1),
                ],
                'Yarr!'
            );
        }

        $offers = $this->addDayOfWeekTrades($dayOfWeek, $leapDay, $offers);
        $offers = $this->addMod17TradesPreciousMetals($dayOfTheYear, $leapDay, $offers);
        $offers = $this->addMod11Trades($dayOfTheYear, $leapDay, $offers);
        $offers = $this->addMod5Trades($dayOfTheYear, $leapDay, $offers);
        $offers = $this->addMod4Trades($dayOfTheYear, $leapDay, $offers);

        if($dayOfTheYear % 3 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_GREENHOUSE_DEED,
                [ TraderOfferCostOrYield::createMoney(100) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Deed for Greenhouse Plot'), 1) ],
                'Oh, cool! Have fun with that!'
            );
        }

        if($leapDay)
        {
            $dialog = "A Leap Day! I wouldn't miss this for anything!\n\nAnd happy Leap Day Birthday to anyone out there who was born on Leap Day! It's gotta' be, like - what? - one in every 1461 people? Something like that!";
            // TODO: add a special Leap Day item, and a special Leap Day Birthday item (Leap Day Birthday Cake?)
            //$offers[] = [];
        }

        return [
            'dialog' => $dialog,
            'offers' => $offers,
        ];
    }

    private function addDayOfWeekTrades(string $dayOfWeek, bool $leapDay, array $offers): array
    {

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_COOKING_BUDDY,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Cooking Buddy'), 1) ],
                'Yay, Cooking Buddy!'
            );

            $offers[] = new TraderOffer(
                self::ID_MOON_PEARL_FOR_10_MONS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Moon Pearl'), 1) ],
                [ TraderOfferCostOrYield::createMoney(10) ],
                $dayOfWeek === 'Mon' ? '10 mons on a Monday! Not bad.' : 'Leap days are wild, huh?'
            );
        }

        if($dayOfWeek === 'Tue' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_WOODEN_SWORD_FOR_5_MONS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wooden Sword'), 1) ],
                [ TraderOfferCostOrYield::createMoney(5) ],
                $dayOfWeek === 'Tue' ? 'Is it just me, or are Tuesdays kind of boring?' : '5 mons on a Leap Day!'
            );

            $offers[] = new TraderOffer(
                self::ID_RAPIER_FOR_10_MONS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Rapier'), 1) ],
                [ TraderOfferCostOrYield::createMoney(10) ],
                $dayOfWeek === 'Tue' ? 'Is it just me, or are Tuesdays kind of boring?' : 'And here you go! 10 mons!'
            );
        }

        if($dayOfWeek === 'Wed' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_QUINT_FOR_FEATHERS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Feathers'), 3) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Quintessence'), 1) ],
                'What\'s the theme of today\'s trade? I\'ll never tell!'
            );
        }

        if($dayOfWeek === 'Thu' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_COMPILER_FOR_BAG_OF_BEANS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Compiler'), 2) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Bag of Beans'), 1) ],
                $dayOfWeek === 'Thu' ? 'It seemed appropriate for Thor\'s Day. (That\'s not a joke! Look it up!)' : 'It\'s like all the rules go out the window on leap days!'
            );

            $offers[] = new TraderOffer(
                self::ID_COMPILER_FOR_10_MONS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Compiler'), 1) ],
                [ TraderOfferCostOrYield::createMoney(13) ],
                $dayOfWeek === 'Thu' ? 'They don\'t call it "13 Mons Thursday" for nothing! (They do call it that, right?)' : 'I like Leap Day. Do you like Leap Day? _I_ like Leap Day.'
            );
        }

        if($dayOfWeek === 'Fri' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_NOT_RED_GOLD_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Yellow Dye'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Witch-hazel'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                ],
                'Sorry: I couldn\'t get my hands on actual Red Gold.'
            );

            $offers[] = new TraderOffer(
                self::ID_NOT_RED_GOLD_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Green Dye'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Witch-hazel'), 1),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Red'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                ],
                'Sorry: I couldn\'t get my hands on actual Red Gold.'
            );
        }

        if($dayOfWeek === 'Sat' || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_GET_YOGURT,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('String'), 2) ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Aging Powder'), 1)
                ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. They also told me to tell anyone who made this trade that you can combine Plain Yogurt, Creamy Milk, and Aging Powder to make _more_ Plain Yogurt. I told them that that\'s basically common knowledge at this point, but they insisted, so... yeah. There you go.'
            );

            $offers[] = new TraderOffer(
                self::ID_SELL_YOGURT,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plain Yogurt'), 2) ],
                [ TraderOfferCostOrYield::createMoney(5) ],
                'I\'m not sure what this deal is about. Those Satyrs put me up to it. I guess they like yogurt?'
            );
        }

        if($dayOfWeek === 'Sun')
        {
            $offers[] = new TraderOffer(
                'sunflower',
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Wheat Flower'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sunflower'), 1) ],
                'Have a nice Sunday!'
            );
        }

        return $offers;
    }

    private function addMod4Trades(int $dayOfTheYear, bool $leapDay, array $offers): array
    {
        if($dayOfTheYear % 4 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_FOR_SWEET_BEET,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silica Grounds'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Sweet Beet'), 1) ],
                'Suh-wEEEEET!'
            );
        }

        if(($dayOfTheYear + 1) % 4 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_TOMATO_FOR_WHITE_CLOTH,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('White Cloth'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Tomato'), 1) ],
                'Tomato Stains on White Cloth are the _worst_. So really, I\'m doing you a favor.'
            );
        }

        if(($dayOfTheYear + 2) % 4 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_FISH_FOR_PAPER,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Fish'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper'), 1) ],
                'Don\'t make fun of me, but I\'m actually kind of scared of fishing. I\'m not scared of Paper-making, though. Brains are funny things.'
            );
        }

        if(($dayOfTheYear + 3) % 4 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_BLUE_CANDY_FOR_WITCH_HAZEL,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blue Hard Candy'), 1 ) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Witch-hazel'), 1) ],
                'Blue\'s my favorite flavor!'
            );
        }

        return $offers;
    }

    private function addMod5Trades(int $dayOfTheYear, bool $leapDay, array $offers): array
    {
        if($dayOfTheYear % 5 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_LEVEL_2_SWORD,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Secret Seashell'), 20) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Level 2 Sword'), 1) ],
                'It\'s dangerous to go alone. Take this.'
            );
        }

        if(($dayOfTheYear + 2) % 5 === 0 || ($dayOfTheYear + 4) % 5 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_LIMESTONE_FOR_ROOTS,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Limestone'), 2),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Ginger'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Grandparoot'), 1),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Carrot'), 1),
                ],
                "I just really need Limestone.\n\nDon't ask."
            );
        }

        if(($dayOfTheYear + 4) % 5 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_3D_PRINTER,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Plastic'), 5),
                    TraderOfferCostOrYield::createMoney(25),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('3D Printer'), 1) ],
                'Too many 3D Printers; not enough Plastic. It\'s a weird problem to have, I know. Thanks for helping me solve it.'
            );
        }

        return $offers;
    }

    private function addMod11Trades(int $dayOfTheYear, bool $leapDay, array $offers): array
    {
        if($dayOfTheYear % 11 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_MUSICAL_SCALES,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Music Note'), 7) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Musical Scales'), 1) ],
                'You can do this yourself at home, by the way. Combine 7 Music Notes into Musical Scales, I mean. It\'s true. Try it out sometime.'
            );
        }

        if(($dayOfTheYear + 4) % 11 === 0 || ($dayOfTheYear + 5) % 11 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_BAG_FOR_PAINTED_ROD,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Painted Fishing Rod'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Paper Bag'), 1) ],
                'What could be insiiiiiiiiiiiiide!!!?!?'
            );
        }

        if(($dayOfTheYear + 6) % 11 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_BOX_BOX_FOR_RIDICULOUS,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('This is Getting Ridiculous'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Box Box'), 1) ],
                'I don\'t remember what exactly is in here, but I\'m pretty sure it\'s just more boxes.'
            );
        }

        if(($dayOfTheYear + 8) % 11 === 0 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_BLACKONITE,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Charcoal'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Dark Matter'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackberries'), 2),
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Black Tea'), 2),
                ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Blackonite'), 1) ],
                'Thaaaaaaaaaaank you!'
            );
        }

        return $offers;
    }

    private function addMod17TradesPreciousMetals(int $dayOfTheYear, bool $leapDay, array $offers): array
    {

        if($dayOfTheYear % 17 === 0 || $dayOfTheYear % 17 === 6 || $dayOfTheYear % 17 === 12 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_GOLD_TO_SILVER_1,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1) ],
                'Thank you kindly.'
            );

            $offers[] = new TraderOffer(
                self::ID_GOLD_TO_SILVER_2,
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1) ],
                [ TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1) ],
                'Thank you kindly.'
            );
        }

        if($dayOfTheYear % 17 === 1 || $dayOfTheYear % 17 === 8 || $dayOfTheYear % 17 === 13 || $leapDay)
        {

            $offers[] = new TraderOffer(
                self::ID_IRON_TO_SILVER_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                    TraderOfferCostOrYield::createMoney(10),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                ],
                'Thank you kindly.'
            );

            $offers[] = new TraderOffer(
                self::ID_IRON_TO_SILVER_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                    TraderOfferCostOrYield::createMoney(8),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                ],
                'Thank you kindly.'
            );
        }

        if($dayOfTheYear % 17 === 2 || $dayOfTheYear % 17 === 9 || $dayOfTheYear % 17 === 15 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_SILVER_TO_IRON_1,
                [TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1)],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Bar'), 1),
                    TraderOfferCostOrYield::createMoney(3),
                ],
                'Thank you kindly.'
            );

            $offers[] = new TraderOffer(
                self::ID_SILVER_TO_IRON_2,
                [TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1)],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Iron Ore'), 1),
                    TraderOfferCostOrYield::createMoney(2),
                ],
                'Thank you kindly.'
            );
        }

        if($dayOfTheYear % 17 === 4 || $dayOfTheYear % 17 === 11 || $dayOfTheYear % 17 === 16 || $leapDay)
        {
            $offers[] = new TraderOffer(
                self::ID_SILVER_TO_GOLD_1,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Bar'), 1),
                    TraderOfferCostOrYield::createMoney(5),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Bar'), 1),
                ],
                'Thank you kindly.'
            );

            $offers[] = new TraderOffer(
                self::ID_SILVER_TO_GOLD_2,
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Silver Ore'), 1),
                    TraderOfferCostOrYield::createMoney(4),
                ],
                [
                    TraderOfferCostOrYield::createItem($this->itemRepository->findOneByName('Gold Ore'), 1),
                ],
                'Thank you kindly.'
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

                default:
                    throw new \InvalidArgumentException('Unexpected cost type "' . $cost->type . '".');
            }
        }

        return true;
    }

    public function makeExchange(User $user, TraderOffer $exchange)
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

                    $user->increaseMoneys(-$cost->quantity);
                    $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $cost->quantity);

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
                        $this->inventoryService->receiveItem($yield->item, $user, null, 'Received by trading with the Trader.', LocationEnum::HOME);
                    break;

                case CostOrYieldTypeEnum::MONEY:
                    $user->increaseMoneys($yield->quantity);
                    break;
            }
        }
    }
}