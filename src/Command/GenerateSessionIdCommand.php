<?php

namespace App\Command;

use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSessionIdCommand extends Command
{
    private $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:generate-session-id')
            ->setDescription('Generates a random session ID.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->sessionService->getSessionId());
    }
}
