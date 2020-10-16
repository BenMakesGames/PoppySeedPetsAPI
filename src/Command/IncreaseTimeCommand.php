<?php

namespace App\Command;

use App\Enum\StatusEffectEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IncreaseTimeCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:increase-time')
            ->setDescription('Increases Time of all Pets by 1, to a maximum of 2880 minutes (48 hours).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // pet logic...
        $this->em->getConnection()->executeQuery('
            LOCK TABLES pet_house_time WRITE, pet WRITE;
            UPDATE pet_house_time LEFT JOIN pet ON pet_id=pet.id SET `activity_time` = `activity_time` + 1 WHERE in_daycare = 0 AND `activity_time` < 2880;
            UPDATE pet_house_time SET `social_energy` = `social_energy` + 1 WHERE `social_energy` < 2880;
            UNLOCK TABLES;
        ');

        // pet group logic...
        $this->em->getConnection()->executeQuery('
            LOCK TABLES pet_group WRITE;
            UPDATE pet_group SET `social_energy` = `social_energy` + 1 WHERE `social_energy` < 2880;
            UNLOCK TABLES;
        ');

        // fireplace logic...
        $this->em->getConnection()->executeQuery('
            LOCK TABLES fireplace WRITE;
            UPDATE fireplace SET longest_streak = current_streak + 1 WHERE current_streak >= longest_streak;
            UPDATE fireplace SET heat = heat - 1, current_streak = current_streak + 1, points = points + 1 WHERE heat > 1;
            UPDATE fireplace SET heat = 0, current_streak = 0, points = points + 1 WHERE heat = 1;
            UNLOCK TABLES;
        ');

        // delete expired sessions...
        $this->em->getConnection()->executeQuery(
            'DELETE FROM user_session WHERE session_expiration<:now',
            [ 'now' => (new \DateTimeImmutable())->format('Y-m-d H:i:s') ]
        );

        // delete old device stats...
        $this->em->getConnection()->executeQuery(
            'DELETE FROM device_stats WHERE time<:oneMonthAgo',
            [ 'oneMonthAgo' => (new \DateTimeImmutable())->modify('-1 month')->format('Y-m-d') ]
        );

        return Command::SUCCESS;
    }
}
