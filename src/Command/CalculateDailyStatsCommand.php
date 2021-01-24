<?php
namespace App\Command;

use App\Entity\DailyStats;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateDailyStatsCommand extends Command
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;

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

        var_dump($oneDayAverages);

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
        ;

        $this->em->persist($dailyStats);
        $this->em->flush();

        return Command::SUCCESS;
    }

    private function getNewPlayerCount(string $firstDate)
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

    private function getAverages(string $firstDate)
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

    private function getLifeTime()
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
