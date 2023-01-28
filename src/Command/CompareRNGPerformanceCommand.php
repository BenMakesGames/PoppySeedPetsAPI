<?php
namespace App\Command;

use App\Service\Squirrel3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareRNGPerformanceCommand extends Command
{
    public const ITERATIONS = 100;

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

    static function NumberOfSetBits($v)
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

        $output->writeln(self::ITERATIONS . ' iterations of mt_rand...');

        $previous = mt_rand(0, 0xFFFFFFFF);

        for($i = 0; $i < self::ITERATIONS; $i++)
        {
            $r = mt_rand(0, 0xFFFFFFFF);

            $mtRandDiffBits[] = CompareRNGPerformanceCommand::NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $squirrel3 = new Squirrel3();
        $squirrel3DiffBits = [];

        $output->writeln(self::ITERATIONS . ' iterations of squirrel3...');

        $previous = $squirrel3->rngNextInt(0, 0xFFFFFFFF);

        for($i = 0; $i < self::ITERATIONS; $i++)
        {
            $r = $squirrel3->rngNextInt(0, 0xFFFFFFFF);

            $squirrel3DiffBits[] = CompareRNGPerformanceCommand::NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $output->writeln('mt_rand % diff  : ' . array_sum($mtRandDiffBits) / self::ITERATIONS);
        $output->writeln('Squirrel3 % diff: ' . array_sum($squirrel3DiffBits) / self::ITERATIONS);

        return Command::SUCCESS;
    }
}
