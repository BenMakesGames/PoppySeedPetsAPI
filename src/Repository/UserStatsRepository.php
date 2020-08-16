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

    private $userStatsPerRequestCache = [];

    public function findOrCreate(User $user, string $name): UserStats
    {
        $cacheKey = $user->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, $this->userStatsPerRequestCache))
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

            $this->userStatsPerRequestCache[$cacheKey] = $stat;
        }

        return $this->userStatsPerRequestCache[$cacheKey];
    }

    public function getStatValue(User $user, string $name): int
    {
        $stat = $this->findOrCreate($user, $name);

        return $stat->getValue();
    }

    public function incrementStat(User $user, string $name, int $change = 1): UserStats
    {
        $stat = $this->findOrCreate($user, $name);

        $stat->increaseValue($change);

        if($name === UserStatEnum::ITEMS_DONATED_TO_MUSEUM)
        {
            if($user->getUnlockedBookstore() === null)
            {
                if($stat->getValue() >= 10)
                    $user->setUnlockedBookstore();
            }

            if($user->getFireplace() && $user->getFireplace()->getMantleSize() === 12)
            {
                if($stat->getValue() >= 400)
                    $user->getFireplace()->setMantleSize(24);
            }
        }

        return $stat;
    }
}
