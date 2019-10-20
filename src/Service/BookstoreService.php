<?php
namespace App\Service;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class BookstoreService
{
    private $userStatsRepository;
    private $userQuestRepository;

    public function __construct(UserStatsRepository $userStatsRepository, UserQuestRepository $userQuestRepository)
    {
        $this->userStatsRepository = $userStatsRepository;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function getAvailableInventory(User $user)
    {
        $bookPrices = [
            'Welcome Note' => 10, // remember: this item can be turned into plain paper
            'Unlocking the Secrets of Grandparoot' => 15,
        ];

        $flowersPurchased = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::FLOWERS_PURCHASED ]);

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

            if($cookedSomething->getValue() >= 35)
                $bookPrices['Pie Recipes'] = 15;

            if($cookedSomething->getValue() >= 50)
                $bookPrices['Milk: The Book'] = 30;
        }

        if($itemsDonatedToMuseum)
        {
            if($itemsDonatedToMuseum->getValue() >= 100)
            {
                $bookPrices['Basement Blueprint'] = 100;
                $bookPrices['The Umbra'] = 25;
            }

            if($itemsDonatedToMuseum->getValue() >= 200)
                $bookPrices['SOUP'] = 25;
        }

        ksort($bookPrices);

        return $bookPrices;
    }
}