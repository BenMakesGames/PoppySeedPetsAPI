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
            ->setDescription('Increases Time of all Pets by 1.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->getConnection()->executeQuery('UPDATE pet SET `time` = `time` + 1 WHERE `time` < 4320');

        //
        $this->em->getConnection()->executeQuery('UPDATE status_effect SET status=:newStatus,time_remaining=total_duration WHERE time_remaining = 1 AND status=:oldStatus', [
            'oldStatus' => StatusEffectEnum::CAFFEINATED,
            'newStatus' => StatusEffectEnum::TIRED
        ]);
        $this->em->getConnection()->executeQuery('DELETE FROM status_effect WHERE time_remaining = 1');
        $this->em->getConnection()->executeQuery('UPDATE status_effect SET time_remaining = time_remaining - 1');
    }
}
