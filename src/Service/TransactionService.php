<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Enum\UserStatEnum;
use App\Functions\PlayerLogHelpers;
use App\Functions\UserStatsHelpers;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    public function spendMoney(User $user, int $amount, string $description, bool $countTotalMoneysSpentStat = true, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMoneys() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough money.');

        $user->increaseMoneys(-$amount);

        if($countTotalMoneysSpentStat)
            UserStatsHelpers::incrementStat($this->em, $user, UserStatEnum::TOTAL_MONEYS_SPENT, $amount);

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (-' . $amount . '~~m~~)', $tags);
    }

    public function getMoney(User $user, int $amount, string $description, array $additionalTags = []): UserActivityLog
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->increaseMoneys($amount);

        $tags = array_merge($additionalTags, [ 'Moneys' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (+' . $amount . '~~m~~)', $tags);
    }

    public function spendRecyclingPoints(User $user, int $amount, string $description, array $additionalTags = [])
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getRecyclePoints() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough recycling points.');

        $user->increaseRecyclePoints(-$amount);

        $tags = array_merge($additionalTags, [ 'Recycling' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (-' . $amount . ' Recycling Point' . ($amount == 1 ? '' : 's') . ')', $tags);
    }

    public function getRecyclingPoints(User $user, int $amount, string $description, array $additionalTags = [])
    {
        if($amount < 0)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($amount >= 1)
            $user->increaseRecyclePoints($amount);

        $tags = array_merge($additionalTags, [ 'Recycling' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (+' . $amount . ' Recycling Point' . ($amount == 1 ? '' : 's') . ')', $tags);
    }

    public function spendMuseumFavor(User $user, int $amount, string $description, array $additionalTags = [])
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        if($user->getMuseumPoints() - $user->getMuseumPointsSpent() < $amount)
            throw new \InvalidArgumentException($user->getName() . ' (#' . $user->getId() . ') does not have enough museum favor.');

        $user->addMuseumPointsSpent($amount);

        $tags = array_merge($additionalTags, [ 'Museum' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (-' . $amount . ' Museum Favor)', $tags);
    }

    public function getMuseumFavor(User $user, int $amount, string $description, array $additionalTags = [])
    {
        if($amount < 1)
            throw new \InvalidArgumentException('$amount must be 1 or greater.');

        $user->addMuseumPoints($amount);

        $tags = array_merge($additionalTags, [ 'Museum' ]);

        return PlayerLogHelpers::create($this->em, $user, $description . ' (+' . $amount . ' Museum Favor)', $tags);
    }
}