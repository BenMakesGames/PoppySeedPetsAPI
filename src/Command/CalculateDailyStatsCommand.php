<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\DailyStats;
use App\Enum\UnlockableFeatureEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateDailyStatsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:calculate-daily-stats')
            ->setDescription('Calculates daily stats!')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable();

        $oneDay = $today->modify('-1 day')->format('Y-m-d');
        $threeDay = $today->modify('-3 days')->format('Y-m-d');
        $week = $today->modify('-7 days')->format('Y-m-d');
        $month = $today->modify('-28 days')->format('Y-m-d');

        $oneDayAverages = $this->getAverages($oneDay);
        $threeDayAverages = $this->getAverages($threeDay);
        $weekAverages = $this->getAverages($week);
        $monthAverages = $this->getAverages($month);
        $lifeTimeAverages = $this->getLifeTime();

        $oneDayNewPlayers = $this->getNewPlayerCount($oneDay);
        $threeDayNewPlayers = $this->getNewPlayerCount($threeDay);
        $weekNewPlayers = $this->getNewPlayerCount($week);
        $monthNewPlayers = $this->getNewPlayerCount($month);

        $dailyStats = (new DailyStats())
            ->setDate($today->modify('-1 day'))

            ->setNumberOfPlayers1Day($oneDayAverages['total_users'])
            ->setNumberOfPlayers3Day($threeDayAverages['total_users'])
            ->setNumberOfPlayers7Day($weekAverages['total_users'])
            ->setNumberOfPlayers28Day($monthAverages['total_users'])
            ->setNumberOfPlayersLifetime($lifeTimeAverages['total_users'])

            ->setTotalMoneys1Day($oneDayAverages['total_moneys'])
            ->setTotalMoneys3Day($threeDayAverages['total_moneys'])
            ->setTotalMoneys7Day($weekAverages['total_moneys'])
            ->setTotalMoneys28Day($monthAverages['total_moneys'])
            ->setTotalMoneysLifetime($lifeTimeAverages['total_moneys'])

            ->setNewPlayers1Day($oneDayNewPlayers['new_users'])
            ->setNewPlayers3Day($threeDayNewPlayers['new_users'])
            ->setNewPlayers7Day($weekNewPlayers['new_users'])
            ->setNewPlayers28Day($monthNewPlayers['new_users'])

            ->setUnlockedTrader1Day($this->getUnlocked(UnlockableFeatureEnum::Trader, $oneDay))
            ->setUnlockedTrader3Day($this->getUnlocked(UnlockableFeatureEnum::Trader, $threeDay))
            ->setUnlockedTrader7Day($this->getUnlocked(UnlockableFeatureEnum::Trader, $week))
            ->setUnlockedTrader28Day($this->getUnlocked(UnlockableFeatureEnum::Trader, $month))
            ->setUnlockedTraderLifetime($this->getLifetimeUnlocked(UnlockableFeatureEnum::Trader))

            ->setUnlockedFireplace1Day($this->getUnlocked(UnlockableFeatureEnum::Fireplace, $oneDay))
            ->setUnlockedFireplace3Day($this->getUnlocked(UnlockableFeatureEnum::Fireplace, $threeDay))
            ->setUnlockedFireplace7Day($this->getUnlocked(UnlockableFeatureEnum::Fireplace, $week))
            ->setUnlockedFireplace28Day($this->getUnlocked(UnlockableFeatureEnum::Fireplace, $month))
            ->setUnlockedFireplaceLifetime($this->getLifetimeUnlocked(UnlockableFeatureEnum::Fireplace))

            ->setUnlockedGreenhouse1Day($this->getUnlocked(UnlockableFeatureEnum::Greenhouse, $oneDay))
            ->setUnlockedGreenhouse3Day($this->getUnlocked(UnlockableFeatureEnum::Greenhouse, $threeDay))
            ->setUnlockedGreenhouse7Day($this->getUnlocked(UnlockableFeatureEnum::Greenhouse, $week))
            ->setUnlockedGreenhouse28Day($this->getUnlocked(UnlockableFeatureEnum::Greenhouse, $month))
            ->setUnlockedGreenhouseLifetime($this->getLifetimeUnlocked(UnlockableFeatureEnum::Greenhouse))

            ->setUnlockedBeehive1Day($this->getUnlocked(UnlockableFeatureEnum::Beehive, $oneDay))
            ->setUnlockedBeehive3Day($this->getUnlocked(UnlockableFeatureEnum::Beehive, $threeDay))
            ->setUnlockedBeehive7Day($this->getUnlocked(UnlockableFeatureEnum::Beehive, $week))
            ->setUnlockedBeehive28Day($this->getUnlocked(UnlockableFeatureEnum::Beehive, $month))
            ->setUnlockedBeehiveLifetime($this->getLifetimeUnlocked(UnlockableFeatureEnum::Beehive))

            ->setUnlockedPortal1Day($this->getUnlocked(UnlockableFeatureEnum::HollowEarth, $oneDay))
            ->setUnlockedPortal3Day($this->getUnlocked(UnlockableFeatureEnum::HollowEarth, $threeDay))
            ->setUnlockedPortal7Day($this->getUnlocked(UnlockableFeatureEnum::HollowEarth, $week))
            ->setUnlockedPortal28Day($this->getUnlocked(UnlockableFeatureEnum::HollowEarth, $month))
            ->setUnlockedPortalLifetime($this->getLifetimeUnlocked(UnlockableFeatureEnum::HollowEarth))
        ;

        $this->em->persist($dailyStats);
        $this->em->flush();

        return self::SUCCESS;
    }

    public function getNewPlayerCount(string $firstDate)
    {
        return $this->em->getConnection()
            ->executeQuery('
                SELECT COUNT(user.id) AS new_users
                FROM user
                WHERE user.registered_on>="' . $firstDate . '"
            ')
            ->fetchAssociative()
        ;
    }

    public function getAverages(string $firstDate)
    {
        return $this->em->getConnection()
            ->executeQuery('
                SELECT
                    COUNT(user.id) AS total_users,
                    SUM(user.moneys) AS total_moneys
                FROM user
                WHERE user.last_activity>="' . $firstDate . '"
            ')
            ->fetchAssociative()
        ;
    }

    public function getUnlocked(string $featureFieldSuffix, string $firstDate): int
    {
        return (int)$this->em->getConnection()
            ->executeQuery('
                SELECT COUNT(user_unlocked_feature.id) AS qty
                FROM user_unlocked_feature
                LEFT JOIN user ON user.id=user_unlocked_feature.user_id
                WHERE
                    user_unlocked_feature.feature="' . $featureFieldSuffix . '"
                    AND user.last_activity>="' . $firstDate . '"
            ')
            ->fetchAssociative()['qty']
        ;
    }

    public function getLifetimeUnlocked(string $featureFieldSuffix): int
    {
        return (int)$this->em->getConnection()
            ->executeQuery('
                SELECT COUNT(user_unlocked_feature.id) AS qty
                FROM user_unlocked_feature
                WHERE
                    user_unlocked_feature.feature="' . $featureFieldSuffix . '"
            ')
            ->fetchAssociative()['qty']
        ;
    }

    public function getLifeTime()
    {
        return $this->em->getConnection()
            ->executeQuery('
                SELECT
                    COUNT(user.id) AS total_users,
                    SUM(user.moneys) AS total_moneys
                FROM user
            ')
            ->fetchAssociative()
        ;
    }
}
