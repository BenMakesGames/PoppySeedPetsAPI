<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Enum\UserStatEnum;
use App\Functions\PlayerLogHelpers;
use App\Repository\UserActivityLogTagRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $em;
    private UserStatsRepository $userStatsRepository;

    public function __construct(
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function spendMoney(User $user, int $amount, string $description, bool $countTotalMoneysSpentStat = true, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMoneys() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough money.');

        $user->increaseMoneys(-$amount);

        if($countTotalMoneysSpentStat)
            $this->userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $amount);

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogHelpers::Create($this->em, $user, $description . ' (' . $amount . '~~m~~)', $tags);
    }

    public function getMoney(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->increaseMoneys($amount);

        // TODO: maybe unlock the market

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogHelpers::Create($this->em, $user, $description . ' (' . $amount . '~~m~~)', $tags);
    }
}