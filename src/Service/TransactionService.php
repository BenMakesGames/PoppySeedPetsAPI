<?php
namespace App\Service;

use App\Entity\TransactionHistory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private $em;
    private $userStatsRepository;

    public function __construct(EntityManagerInterface $em, UserStatsRepository $userStatsRepository)
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function spendMoney(User $user, int $amount, string $description, bool $countTotalMoneysSpentStat = true): TransactionHistory
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMoneys() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough money.');

        $user->increaseMoneys(-$amount);

        if($countTotalMoneysSpentStat)
            $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $amount);

        $transaction = (new TransactionHistory())
            ->setUser($user)
            ->setAmount(-$amount)
            ->setDescription($description)
        ;

        $this->em->persist($transaction);

        return $transaction;
    }

    public function getMoney(User $user, int $amount, string $description): TransactionHistory
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->increaseMoneys($amount);

        $transaction = (new TransactionHistory())
            ->setUser($user)
            ->setAmount($amount)
            ->setDescription($description)
        ;

        $this->em->persist($transaction);

        return $transaction;
    }
}