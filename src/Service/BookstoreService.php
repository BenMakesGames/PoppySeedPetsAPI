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
            'Cooking 101' => 15,
        ];

        $flowersPurchased = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::FLOWERS_PURCHASED ]);

        if($flowersPurchased && $flowersPurchased->getValue() > 0)
            $bookPrices['Book of Flowers'] = 15;

        $cookedSomething = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::COOKED_SOMETHING ]);
        $itemsDonatedToMuseum = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::ITEMS_DONATED_TO_MUSEUM ]);
        $petsBirthed = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::PETS_BIRTHED ]);
        $petsAdopted = $this->userStatsRepository->findOneBy([ 'user' => $user, 'stat' => UserStatEnum::PETS_ADOPTED ]);

        if($cookedSomething)
        {
            if($cookedSomething->getValue() >= 5)
                $bookPrices['Candy-maker\'s Cookbook'] = 20;

            if($cookedSomething->getValue() >= 10)
                $bookPrices['Big Book of Baking'] = 25;

            if($cookedSomething->getValue() >= 20)
                $bookPrices['Fish Book'] = 20;

            if($cookedSomething->getValue() >= 25)
                $bookPrices['Book of Noods'] = 20;

            if($cookedSomething->getValue() >= 35)
                $bookPrices['Pie Recipes'] = 15;

            if($cookedSomething->getValue() >= 50)
            {
                $bookPrices['Milk: The Book'] = 30;
                $bookPrices['Fried'] = 25;
            }
        }

        if($itemsDonatedToMuseum)
        {
            if($itemsDonatedToMuseum->getValue() >= 100)
            {
                $bookPrices['Basement Blueprint'] = 100;
                $bookPrices['The Umbra'] = 25;
            }

            if($itemsDonatedToMuseum->getValue() >= 150)
                $bookPrices['Electrical Engineering Textbook'] = 50;

            if($itemsDonatedToMuseum->getValue() >= 200)
                $bookPrices['SOUP'] = 25;
        }

        if($user->getGreenhouse() && $user->getGreenhouse()->getMaxPlants() > 6)
        {
            $bookPrices['Bird Bath Blueprint'] = 200;
        }

        $petsAcquired = ($petsBirthed ? $petsBirthed->getValue() : 0) + ($petsAdopted ? $petsAdopted->getValue() / 10 : 0);

        if($petsAcquired >= 3)
        {
            $divideBy = 1;

            if($itemsDonatedToMuseum->getValue() >= 800)
                $divideBy = 2;
            else if($itemsDonatedToMuseum->getValue() >= 600)
                $divideBy = 1.75;
            else if($itemsDonatedToMuseum->getValue() >= 400)
                $divideBy = 1.5;
            else if($itemsDonatedToMuseum->getValue() >= 200)
                $divideBy = 1.25;

            $bookPrices['Renaming Scroll'] = ceil(max(500, ceil(860 - 20 * $petsAcquired)) / $divideBy);
        }

        ksort($bookPrices);

        return $bookPrices;
    }
}
