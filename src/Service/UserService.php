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
        $stat = $this->userStatsRepository->getStat($user, UserStatEnum::PETS_ADOPTED);

        if($stat->getValue() === 0)
            return 50;
        else if($stat->getValue() <= 2)
            return 100;
        else if($stat->getValue() <= 10)
            return 150;
        else if($stat->getValue() <= 50)
            return 200;
        else if($stat->getValue() <= 200)
            return 300;
        else if($stat->getValue() <= 1000)
            return 500;
        else
            return 1000;
    }
}