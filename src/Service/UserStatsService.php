<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\UserStat;
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

    private static function getCacheKey(User $user, string $statName): string
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

                $stat = new UserStats(user: $user, stat: $statName);

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

        if($name === UserStat::ItemsBoughtInMarket)
        {
            if($oldValue < 50 && $stat->getValue() >= 50)
                $user->increaseMaxMarketBids(5);
        }
        else if($name === UserStat::ItemsSoldInMarket)
        {
            if($oldValue < 50 && $stat->getValue() >= 50)
                $user->increaseMaxMarketBids(5);
        }
        else if($name === UserStat::ItemsDonatedToMuseum)
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
