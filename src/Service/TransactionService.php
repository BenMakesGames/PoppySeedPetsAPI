<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Enum\UserStatEnum;
use App\Repository\UserActivityLogTagRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $em;
    private UserStatsRepository $userStatsRepository;
    private UserActivityLogTagRepository $activityLogTagRepository;

    public function __construct(
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        UserActivityLogTagRepository $activityLogTagRepository
    )
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->activityLogTagRepository = $activityLogTagRepository;
    }

    public function spendMoney(User $user, int $amount, string $description, bool $countTotalMoneysSpentStat = true): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMoneys() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough money.');

        $user->increaseMoneys(-$amount);

        if($countTotalMoneysSpentStat)
            $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $amount);

        $transaction = (new UserActivityLog())
            ->setUser($user)
            ->setEntry($description . ' (' . $amount . '~~m~~)')
            ->addTags($this->activityLogTagRepository->findByNames([ 'Moneys' ]))
        ;

        $this->em->persist($transaction);

        return $transaction;
    }

    public function getMoney(User $user, int $amount, string $description): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->increaseMoneys($amount);

        $transaction = (new UserActivityLog())
            ->setUser($user)
            ->setEntry($description . ' (' . $amount . '~~m~~)')
            ->addTags($this->activityLogTagRepository->findByNames([ 'Moneys' ]))
        ;

        $this->em->persist($transaction);

        return $transaction;
    }
}