<?php
namespace App\Command;

use App\Service\Squirrel3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareRNGPerformanceCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:compare-rng-performance')
            ->setDescription('Compare performance of mt_rand to squirrel3Noise.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hold on. This will take 20 seconds.');

        mt_srand();
        $mtRandCallCount = 0;

        $output->writeln('Timing mt_rand...');

        $startMtRand = microtime(true);

        while(microtime(true) - $startMtRand < 10)
        {
            mt_rand(0, 115249);
            $mtRandCallCount++;
        }

        $squirrel3 = new Squirrel3();
        $squirrel3CallCount = 0;

        $output->writeln('Timing Squirrel3...');

        $startSquirrel3 = microtime(true);

        while(microtime(true) - $startSquirrel3 < 10)
        {
            $squirrel3->rngNextInt(0, 115249);
            $squirrel3CallCount++;
        }

        $output->writeln('In 10 seconds...');
        $output->writeln('Squirrel3: ' . $squirrel3CallCount);
        $output->writeln('mt_rand  : ' . $mtRandCallCount);

        return Command::SUCCESS;
    }
}
