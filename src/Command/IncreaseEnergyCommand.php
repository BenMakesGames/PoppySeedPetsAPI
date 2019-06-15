<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IncreaseEnergyCommand extends Command
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
            ->setName('app:increase-energy')
            ->setDescription('Increases energy of all pets by 1.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->getConnection()->executeQuery('UPDATE pets SET energy=energy+1 WHERE energy<4320');
    }
}
