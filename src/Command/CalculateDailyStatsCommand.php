<?php
namespace App\Command;

use App\Entity\DailyStats;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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

    protected function configure()
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

            ->setUnlockedTrader1Day($this->getUnlocked('trader', $oneDay))
            ->setUnlockedTrader3Day($this->getUnlocked('trader', $threeDay))
            ->setUnlockedTrader7Day($this->getUnlocked('trader', $week))
            ->setUnlockedTrader28Day($this->getUnlocked('trader', $month))
            ->setUnlockedTraderLifetime($this->getLifetimeUnlocked('trader'))

            ->setUnlockedFireplace1Day($this->getUnlocked('fireplace', $oneDay))
            ->setUnlockedFireplace3Day($this->getUnlocked('fireplace', $threeDay))
            ->setUnlockedFireplace7Day($this->getUnlocked('fireplace', $week))
            ->setUnlockedFireplace28Day($this->getUnlocked('fireplace', $month))
            ->setUnlockedFireplaceLifetime($this->getLifetimeUnlocked('fireplace'))

            ->setUnlockedGreenhouse1Day($this->getUnlocked('greenhouse', $oneDay))
            ->setUnlockedGreenhouse3Day($this->getUnlocked('greenhouse', $threeDay))
            ->setUnlockedGreenhouse7Day($this->getUnlocked('greenhouse', $week))
            ->setUnlockedGreenhouse28Day($this->getUnlocked('greenhouse', $month))
            ->setUnlockedGreenhouseLifetime($this->getLifetimeUnlocked('greenhouse'))

            ->setUnlockedBeehive1Day($this->getUnlocked('beehive', $oneDay))
            ->setUnlockedBeehive3Day($this->getUnlocked('beehive', $threeDay))
            ->setUnlockedBeehive7Day($this->getUnlocked('beehive', $week))
            ->setUnlockedBeehive28Day($this->getUnlocked('beehive', $month))
            ->setUnlockedBeehiveLifetime($this->getLifetimeUnlocked('beehive'))

            ->setUnlockedPortal1Day($this->getUnlocked('hollow_earth', $oneDay))
            ->setUnlockedPortal3Day($this->getUnlocked('hollow_earth', $threeDay))
            ->setUnlockedPortal7Day($this->getUnlocked('hollow_earth', $week))
            ->setUnlockedPortal28Day($this->getUnlocked('hollow_earth', $month))
            ->setUnlockedPortalLifetime($this->getLifetimeUnlocked('hollow_earth'))
        ;

        $this->em->persist($dailyStats);
        $this->em->flush();

        return Command::SUCCESS;
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
                SELECT COUNT(user.id) AS qty
                FROM user
                WHERE
                    unlocked_' . $featureFieldSuffix . ' IS NOT NULL
                    AND user.last_activity>="' . $firstDate . '"
            ')
            ->fetchAssociative()['qty']
        ;
    }

    public function getLifetimeUnlocked(string $featureFieldSuffix): int
    {
        return (int)$this->em->getConnection()
            ->executeQuery('
                SELECT COUNT(user.id) AS qty
                FROM user
                WHERE unlocked_' . $featureFieldSuffix . ' IS NOT NULL
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
