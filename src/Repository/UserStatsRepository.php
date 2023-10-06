<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStats[]    findAll()
 * @method UserStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserStats::class);
    }

    private static $userStatsPerRequestCache = [];

    public static function findOrCreate(EntityManagerInterface $em, User $user, string $name): UserStats
    {
        $cacheKey = $user->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, self::$userStatsPerRequestCache))
        {
            $stat = $em->getRepository(UserStatsRepository::class)->findOneBy([
                'user' => $user,
                'stat' => $name
            ]);

            if(!$stat)
            {
                $stat = (new UserStats())
                    ->setUser($user)
                    ->setStat($name)
                ;

                $em->persist($stat);
            }

            self::$userStatsPerRequestCache[$cacheKey] = $stat;
        }

        return self::$userStatsPerRequestCache[$cacheKey];
    }

    public static function getStatValue(EntityManagerInterface $em, User $user, string $name): int
    {
        $stat = self::findOrCreate($em, $user, $name);

        return $stat->getValue();
    }

    public static function incrementStat(EntityManagerInterface $em, User $user, string $name, int $change = 1): UserStats
    {
        $stat = self::findOrCreate($em, $user, $name);

        $oldValue = $stat->getValue();

        $stat->increaseValue($change);

        if($name === UserStatEnum::ITEMS_BOUGHT_IN_MARKET)
        {
            if($oldValue < 50 && $stat->getValue() >= 50)
                $user->increaseMaxMarketBids(5);
        }
        else if($name === UserStatEnum::ITEMS_SOLD_IN_MARKET)
        {
            if($oldValue < 50 && $stat->getValue() >= 50)
                $user->increaseMaxMarketBids(5);
        }
        else if($name === UserStatEnum::ITEMS_DONATED_TO_MUSEUM)
        {
            if($oldValue < 400 && $stat->getValue() >= 400)
            {
                if($user->getFireplace())
                    $user->getFireplace()->setMantleSize(24);

                $user->increaseMaxMarketBids(10);
            }
        }

        return $stat;
    }
}
