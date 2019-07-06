<?php
namespace App\Service;
use App\Entity\User;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;

class BookStoreService
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
            'Welcome Note' => 10,
        ];

        $flowersPurchased = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => 'Flowers Purchased' ]);

        if($flowersPurchased && $flowersPurchased->getValue() > 0)
            $bookPrices['Book of Flowers'] = 15;

        $cookedSomething = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => 'Cooked Something' ]);

        if($cookedSomething && $cookedSomething->getValue() >= 5)
            $bookPrices['Candy Cookbook'] = 20;

        return $bookPrices;
    }
}