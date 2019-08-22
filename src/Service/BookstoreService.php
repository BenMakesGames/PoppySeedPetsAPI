<?php
namespace App\Service;
use App\Entity\User;
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

        $flowersPurchased = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => 'Flowers Purchased' ]);

        if($flowersPurchased && $flowersPurchased->getValue() > 0)
            $bookPrices['Book of Flowers'] = 15;

        $cookedSomething = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => 'Cooked Something' ]);

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
        }

        ksort($bookPrices);

        return $bookPrices;
    }
}