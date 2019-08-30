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

        else if($stat->getValue() <= 6)
            return 50;
        else if($stat->getValue() <= 28)
            return 75;
        else if($stat->getValue() <= 496)
            return 100;
        else if($stat->getValue() <= 8128)
            return 50;
        else
            return 10;
    }
}