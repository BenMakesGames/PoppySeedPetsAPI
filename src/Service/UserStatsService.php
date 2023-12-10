<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\UserStatEnum;
use App\Functions\InMemoryCache;
use Doctrine\ORM\EntityManagerInterface;

class UserStatsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InMemoryCache $perRequestCache
    )
    {
    }

    private static function getCacheKey(User $user, string $statName)
    {
        return 'UserStats:' . $user->getId() . '-' . $statName;
    }

    private function findOrCreate(User $user, string $statName): UserStats
    {
        return $this->perRequestCache->get(
            self::getCacheKey($user, $statName),
            function() use ($user, $statName) {
                $stat = $this->em->getRepository(UserStats::class)->findOneBy([
                    'user' => $user,
                    'stat' => $statName
                ]);

                if($stat)
                    return $stat;

                $stat = (new UserStats())
                    ->setUser($user)
                    ->setStat($statName)
                ;

                $this->em->persist($stat);

                return $stat;
            }
        );
    }

    public function getStatValue(User $user, string $name): int
    {
        $stat = $this->findOrCreate($user, $name);

        return $stat->getValue();
    }

    public function incrementStat(User $user, string $name, int $change = 1): UserStats
    {
        $stat = $this->findOrCreate($user, $name);

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
