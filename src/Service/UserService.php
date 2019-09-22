<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;

class UserService
{
    private $userStatsRepository;

    public function __construct(UserStatsRepository $userStatsRepository)
    {
        $this->userStatsRepository = $userStatsRepository;
    }

    public function getAdoptionFee(User $user): int
    {
        $statValue = $this->userStatsRepository->getStatValue($user, UserStatEnum::PETS_ADOPTED);

        if($statValue <= 6)
            return 50;
        else if($statValue <= 28)
            return 75;
        else if($statValue <= 496)
            return 100;
        else if($statValue <= 8128)
            return 50;
        else
            return 10;
    }
}