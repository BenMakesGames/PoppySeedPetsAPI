<?php
namespace App\Service;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class TravelingMerchantService
{
    private const ID_PROOF_OF_ADVENTURING = 1;
    private const ID_LEVEL_2_SWORD = 2;

    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
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

        if($dayOfWeek === 'Mon' || $leapDay)
        {
            $offers = [
                [
                    'id' => self::ID_PROOF_OF_ADVENTURING,
                    'cost' => [
                        [ 'item' => $this->itemRepository->findOneByName('Moon Pearl'), 'quantity' => 1 ],
                        [ 'item' => $this->itemRepository->findOneByName('Outrageously Strongbox'), 'quantity' => 1 ],
                        [ 'item' => $this->itemRepository->findOneByName('Naner Puddin\''), 'quantity' => 1 ],
                    ],
                    'yield' => [
                        [ 'item' => $this->itemRepository->findOneByName('Proof of Adventuring'), 'quantity' => 1 ],
                    ],
                    'comment' => 'I just really like Naner Puddin\'.'
                ]
            ];
        }

        if($dayOfTheYear % 5 === 0 || $leapDay)
        {
            $offers = [
                [
                    'id' => self::ID_LEVEL_2_SWORD,
                    'cost' => [
                        [ 'item' => $this->itemRepository->findOneByName('Secret Seashell'), 'quantity' => 20 ],
                    ],
                    'yield' => [
                        [ 'item' => $this->itemRepository->findOneByName('Level 2 Sword'), 'quantity' => 1 ],
                    ],
                    'comment' => 'It\'s dangerous to go alone. Take this!'
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
}