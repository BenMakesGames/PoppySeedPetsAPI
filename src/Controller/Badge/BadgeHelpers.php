<?php
namespace App\Controller\Badge;

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\BadgeEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\EntityManagerInterface;

final class BadgeHelpers
{
    private static $perRequestStatTotalCache = [];

    public static function getStatTotal(EntityManagerInterface $em, User $user, array $statNames): int
    {
        $key = $user->getId() . ':' . implode(',', $statNames);

        if(!array_key_exists($key, self::$perRequestStatTotalCache))
        {
            self::$perRequestStatTotalCache[$key] = (int)($em->createQueryBuilder()
                ->select('SUM(s.value)')
                ->from(UserStats::class, 's')
                ->andWhere('s.user = :user')
                ->andWhere('s.stat IN (:stats)')
                ->setParameter('user', $user)
                ->setParameter('stats', $statNames)
                ->getQuery()
                ->getSingleScalarResult());
        }

        return self::$perRequestStatTotalCache[$key];
    }

    public static function getBadgeProgress(string $badge, User $user, EntityManagerInterface $em): array
    {
        switch($badge)
        {
            case BadgeEnum::RECYCLED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                break;

            case BadgeEnum::RECYCLED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                break;

            case BadgeEnum::BAABBLES_OPENED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                break;

            case BadgeEnum::BAABBLES_OPENED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                break;

            case BadgeEnum::BAABBLES_OPENED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                break;

            default:
                throw new \Exception('Oops! Badge not implemented! Ben was a bad programmer!');
        }

        return [
            'badge' => $badge,
            'progress' => $progress,
            'done' => $progress['current'] >= $progress['target'],
        ];
    }

}