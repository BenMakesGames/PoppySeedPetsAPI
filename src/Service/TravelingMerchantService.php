<?php
namespace App\Service;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class TravelingMerchantService
{
    private const ID_PROOF_OF_ADVENTURING = 1;
    private const ID_LEVEL_2_SWORD = 2;
    private const ID_RUSTY_RAPIER = 3;
    private const ID_TOMATO_1 = 4;
    private const ID_TOMATO_2 = 5;

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

    public function getOffers()
    {
        $now = new \DateTimeImmutable();

        $dialog = 'Check out what I\'ve got.';
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

        if($date === 'Sep 19')
        {
            $offers = [
                [
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
                ]
            ];
        }

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            $offers = [
                [
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
                ]
            ];
        }

        if($dayOfWeek === 'Tue' || $leapDay)
        {
            $offers = [
                [
                    'id' => self::ID_TOMATO_1,
                    'cost' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Tea Leaves'), 'quantity' => 1 ],
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Toadstool'), 'quantity' => 1 ],
                    ],
                    'yield' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Tomato'), 'quantity' => 1 ],
                    ],
                    'comment' => 'Happy Tuesday!'
                ],
                [
                    'id' => self::ID_TOMATO_2,
                    'cost' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Toad Legs'), 'quantity' => 1 ],
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Talon'), 'quantity' => 1 ],
                    ],
                    'yield' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Tomato'), 'quantity' => 2 ],
                    ],
                    'comment' => 'Happy Tuesday!'
                ],
            ];
        }

        if($dayOfTheYear % 5 === 0 || $leapDay)
        {
            $offers = [
                [
                    'id' => self::ID_LEVEL_2_SWORD,
                    'cost' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Secret Seashell'), 'quantity' => 20 ],
                    ],
                    'yield' => [
                        [ 'type' => 'item', 'item' => $this->itemRepository->findOneByName('Level 2 Sword'), 'quantity' => 1 ],
                    ],
                    'comment' => 'It\'s dangerous to go alone. Take this.'
                ]
            ];
        }

        if($leapDay)
        {
            $dialog = "A Leap Day! I wouldn't miss this for anything!\n\nAnd happy Leap Day Birthday to anyone out there who was born on Leap Day! It's gotta' be, like - what? - one in every 1461ish people? Something like that!";
            // TODO: add a special Leap Day item, and a special Leap Day Birthday item (Leap Day Birthday Cake?)
            //$offers[] = [];
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
                    $quantity = $this->inventoryService->loseItem($user, $cost['item'], $cost['quantity']);

                    if($quantity < $cost['quantity'])
                        throw new \InvalidArgumentException('user does not have the items needed to make this exchange.');

                    break;

                case 'money':
                    $user->increaseMoneys(-$cost['quantity']);
                    $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $cost['quantity']);

                    break;
            }
        }

        foreach($exchange['yield'] as $yield)
        {

        }
    }
}