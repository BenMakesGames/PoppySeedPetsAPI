<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\UserStatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStats[]    findAll()
 * @method UserStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserStats::class);
    }

    public function getStatValue(User $user, string $name): int
    {
        $stat = $this->findOneBy([
            'user' => $user,
            'stat' => $name
        ]);

        return $stat ? $stat->getValue() : 0;
    }

    public function incrementStat(User $user, string $name, int $change = 1): UserStats
    {
        $stat = $this->findOneBy([
            'user' => $user,
            'stat' => $name
        ]);

        if(!$stat)
        {
            $stat = (new UserStats())
                ->setUser($user)
                ->setStat($name)
            ;

            $this->getEntityManager()->persist($stat);
        }

        $stat->increaseValue($change);

        if($user->getUnlockedBookstore() === null && ($name === UserStatEnum::ITEMS_THROWN_AWAY || $name === UserStatEnum::ITEMS_DONATED_TO_MUSEUM))
        {
            $tossedAndDonated = $this->findBy([ 'user' => $user, 'stat' => [ UserStatEnum::ITEMS_THROWN_AWAY, UserStatEnum::ITEMS_DONATED_TO_MUSEUM ] ]);
            $total = array_sum(array_map(function(UserStats $s) { return $s->getValue(); }, $tossedAndDonated));
            if($total >= 10)
                $user->setUnlockedBookstore();
        }

        return $stat;
    }
}
