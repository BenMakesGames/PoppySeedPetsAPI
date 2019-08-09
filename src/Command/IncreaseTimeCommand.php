<?php

namespace App\Command;

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
            ->setDescription('Increases Time of all Pets by 1.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->getConnection()->executeQuery('UPDATE pet SET `time`=`time`+1 WHERE `time`<4320');
        $this->em->getConnection()->executeQuery('UPDATE greenhouse_plant SET `weeds`=`weeds`+1 WHERE `weeds`<10080'); // 10080 is the number of minutes in a week
    }
}
