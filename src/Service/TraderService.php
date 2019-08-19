<?php
namespace App\Service;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
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

    private $itemRepository;
    private $inventoryService;
    private $userStatsRepository;

    // to count item quantities:
    public const TOO_MANY = [
        'Aging Powder' => 4,
        'Fish' => 3,
        'Black Tea' => 3,
        'Toadstool' => 2,
        'String' => 2,
        'Onion' => 1.3333,
        'Egg' => 1.3333,
        'Mermaid Egg' => 1,
        'Painted Fishing Rod' => 0.6666,
        'Fiberglass' => 0.5,
    ];

    public const NOT_ENOUGH = [
        'Wheat' => 1,
        'Rice' => 1,
        'Baker\'s Yeast' => 1,
        'Cooking Buddy' => 0.0666,
        'Feathers' => 1,
        'Iron Ore' => 0.25,
        'Smallish Pumpkin' => 0.5,
        'Cream of Tartar' => 2,
        'Naner' => 1,
        'Quintessence' => 0.15,
        'Plain Yogurt' => 0.6666,
        'Witch-hazel' => 1,
        'Bag of Beans' =>  0.1,
        'Tomato' => 1,
        'Sweet Beet' => 1,
        '5~~m~~' => 1,
    ];

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
        mt_srand($user->getDailySeed());

        $numOffers = 5;

        $now = new \DateTimeImmutable();

        $dialog = ArrayFunctions::pick_one([
            'My offerings change daily.',
            'Don\'t see anything you like? Check back tomorrow.',
            'Different day, different deals!',
        ]);

        $offers = [];

        $date = $now->format('M j');
        $dayOfWeek = $now->format('D');
        $dayOfTheYear = (int)$now->format('z');

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
            $offers[] = [
                'id' => self::ID_RUSTY_RAPIER,
                'cost' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Scales'), 'quantity' => 1 ],
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Seaweed'), 'quantity' => 1 ],
                    [ 'type' => 'money', 'quantity' => 10 ]
                ],
                'yield' => [
                    [ 'item' => $this->itemRepository->findOneByName('Rusty Rapier'), 'quantity' => 1 ],
                ],
                'comment' => 'Yarr!'
            ];
        }

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            /*$offers[] = [
                'id' => self::ID_PROOF_OF_ADVENTURING,
                'cost' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Moon Pearl'), 'quantity' => 1 ],
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Outrageously Strongbox'), 'quantity' => 1 ],
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Naner Puddin\''), 'quantity' => 1 ],
                ],
                'yield' => [
                    [ 'item' => $this->itemRepository->findOneByName('Proof of Adventuring'), 'quantity' => 1 ],
                ],
                'comment' => 'I just really like Naner Puddin\'.'
            ];*/
        }

        if($dayOfWeek === 'Tue')
            $numOffers++;
        else if($dayOfWeek === 'Thu')
            $numOffers--;

        if($dayOfTheYear % 5 === 0 || $leapDay)
        {
            $offers[] = [
                'id' => self::ID_LEVEL_2_SWORD,
                'cost' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Secret Seashell'), 'quantity' => 20 ],
                ],
                'yield' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Level 2 Sword'), 'quantity' => 1 ],
                ],
                'comment' => 'It\'s dangerous to go alone. Take this.'
            ];
        }

        if($dayOfTheYear % 3 === 0 || $leapDay)
        {
            $offers[] = [
                'id' => self::ID_GREENHOUSE_DEED,
                'cost' => [
                    [ 'type' => 'money', 'quantity' => 100 ],
                ],
                'yield' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Deed for Greenhouse Plot'), 'quantity' => 1 ],
                ],
                'comment' => 'Oh, cool! Have fun with that!'
            ];
        }

        if($leapDay)
        {
            $dialog = "A Leap Day! I wouldn't miss this for anything!\n\nAnd happy Leap Day Birthday to anyone out there who was born on Leap Day! It's gotta' be, like - what? - one in every 1461ish people? Something like that!";
            // TODO: add a special Leap Day item, and a special Leap Day Birthday item (Leap Day Birthday Cake?)
            //$offers[] = [];
        }

        $asking = self::TOO_MANY;
        $offering = self::NOT_ENOUGH;
        $offerOffset = mt_rand(1000, 9990);

        for($i = 0; $i < $numOffers; $i++)
        {
            $askingItem = array_rand($asking);
            $askingQuantity = $asking[$askingItem];

            $offeringItem = array_rand($offering);
            $offeringQuantity = $offering[$offeringItem];

            if($askingQuantity < 1)
            {
                $offeringQuantity = $offeringQuantity / $askingQuantity;
                $askingQuantity = 1;
            }

            if($offeringQuantity < 1)
            {
                $askingQuantity = round($askingQuantity / $offeringQuantity);
                $offeringQuantity = 1;
            }
            else if($offeringQuantity > 1 && $askingQuantity % $offeringQuantity === 0)
            {
                $askingQuantity /= $offeringQuantity;
                $offeringQuantity = 1;
            }

            $offeringQuantity = round($offeringQuantity);
            $askingQuantity = round($askingQuantity);

            if($askingQuantity > 20)
                continue;

            unset($asking[$askingItem]);
            unset($offering[$offeringItem]);

            $cost = [
                'type' => 'item',
                'item' => $this->itemRepository->findOneByName($askingItem),
                'quantity' => $askingQuantity
            ];

            if(preg_match('/^[0-9]+~~m~~$/', $offeringItem))
            {
                $yield = [
                    'type' => 'money',
                    'quantity' => (int)$offeringItem,
                ];
            }
            else
            {
                $yield = [
                    'type' => 'item',
                    'item' => $this->itemRepository->findOneByName($offeringItem),
                    'quantity' => $offeringQuantity
                ];
            }

            $offers[] = [
                'id' => 'dailyOffer' . ($i + $offerOffset) . $now->format('d'),
                'cost' => [ $cost ],
                'yield' => [ $yield ],
                'comment' => 'Great! Enjoy the ' . $offeringItem . '!',
            ];
        }

        if($dayOfWeek === 'Sun')
        {
            $askingItem = array_rand($asking);
            $askingQuantity = $asking[$askingItem];
            unset($asking[$askingItem]);

            $offers[] = [
                'id' => 'sunflower',
                'cost' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName($askingItem), 'quantity' => ceil($askingQuantity) ]
                ],
                'yield' => [
                    [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Sunflower'), 'quantity' => 1 ]
                ],
                'comment' => 'Have a nice Sunday!',
            ];
        }

        return [
            'dialog' => $dialog,
            'offers' => $offers,
        ];
    }

    public function userCanMakeExchange(User $user, $exchange): bool
    {
        foreach($exchange['cost'] as $cost)
        {
            switch($cost['type'])
            {
                case 'item':
                    $quantity = $this->inventoryService->countInventory($user, $cost['item']);

                    if($quantity < $cost['quantity'])
                        return false;

                    break;

                case 'money':
                    if($user->getMoneys() < $cost['quantity'])
                        return false;

                    break;
            }
        }

        return true;
    }

    public function makeExchange(User $user, $exchange)
    {
        foreach($exchange['cost'] as $cost)
        {
            switch($cost['type'])
            {
                case 'item':
                    $quantity = $this->inventoryService->loseItem($cost['item'], $user, $cost['quantity']);

                    if($quantity < $cost['quantity'])
                        throw new \InvalidArgumentException('You do not have the items needed to make this exchange.');

                    break;

                case 'money':
                    if($cost['quantity'] > $user->getMoneys())
                        throw new \InvalidArgumentException('You do not have the moneys needed to make this exchange.');

                    $user->increaseMoneys(-$cost['quantity']);
                    $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $cost['quantity']);

                    break;
            }
        }

        foreach($exchange['yield'] as $yield)
        {
            switch($yield['type'])
            {
                case 'item':
                    for($i = 0; $i < $yield['quantity']; $i++)
                        $this->inventoryService->receiveItem($yield['item'], $user, null, 'Received by trading with the Trader.');
                    break;

                case 'money':
                    $user->increaseMoneys($yield['quantity']);
                    break;
            }
        }
    }
}