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

    function NumberOfSetBits($v)
    {
        $c = $v - (($v >> 1) & 0x55555555);
        $c = (($c >> 2) & 0x33333333) + ($c & 0x33333333);
        $c = (($c >> 4) + $c) & 0x0F0F0F0F;
        $c = (($c >> 8) + $c) & 0x00FF00FF;
        $c = (($c >> 16) + $c) & 0x0000FFFF;
        return $c;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        mt_srand();
        $mtRandDiffBits = [];

        $output->writeln('Testing mt_rand...');

        $previous = mt_rand(0, 0xFFFFFFFF);

        for($i = 0; $i < 100000; $i++)
        {
            $r = mt_rand(0, 0xFFFFFFFF);

            $mtRandDiffBits[] = $this->NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $squirrel3 = new Squirrel3();
        $squirrel3DiffBits = [];

        $output->writeln('Timing Squirrel3...');

        $previous = $squirrel3->rngNextInt(0, 0xFFFFFFFF);

        for($i = 0; $i < 100000; $i++)
        {
            $r = $squirrel3->rngNextInt(0, 0xFFFFFFFF);

            $squirrel3DiffBits[] = $this->NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $output->writeln('mt_rand % diff  : ' . array_sum($mtRandDiffBits) / 100000);
        $output->writeln('Squirrel3 % diff: ' . array_sum($squirrel3DiffBits) / 100000);

        return Command::SUCCESS;
    }
}
