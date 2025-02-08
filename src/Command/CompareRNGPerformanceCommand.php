<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Squirrel3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareRNGPerformanceCommand extends Command
{
    public const ITERATIONS = 10000;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
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
        echo "\n";

        // mt_rand test:
        echo self::ITERATIONS . ' iterations of mt_rand...' . "\n";

        [ $mtRandDiffBits, $mtRandTime ] = $this->testMtRand();

        echo 'time   : ' . round($mtRandTime * 1000, 4) . 'ms' . "\n";
        echo '%diff  : ' . (array_sum($mtRandDiffBits) / self::ITERATIONS) . "\n";
        echo "\n";

        // random_int test
        echo self::ITERATIONS . ' iterations of random_int...' . "\n";

        [ $randomIntDiffBits, $randomIntTime ] = $this->testRandomInt();

        echo 'time   : ' . round($randomIntTime * 1000, 4) . 'ms' . "\n";
        echo '%diff  : ' . (array_sum($randomIntDiffBits) / self::ITERATIONS) . "\n";
        echo "\n";

        // Squirrel3 test:
        echo self::ITERATIONS . ' iterations of squirrel3...' . "\n";

        [ $squirrel3DiffBits, $squirrel3Time ] = $this->testSquirrel3();

        echo 'time   : ' . round($squirrel3Time * 1000, 4) . 'ms' . "\n";
        echo '%diff  : ' . (array_sum($squirrel3DiffBits) / self::ITERATIONS) . "\n";
        echo "\n";

        return self::SUCCESS;
    }

    public function testMtRand(): array
    {
        mt_srand();
        $mtRandDiffBits = [];

        $previous = mt_rand(0, 0xFFFFFFFF); // to test bit distribution, we must ask for bits in even distribution

        for($i = 0; $i < self::ITERATIONS; $i++)
        {
            $r = mt_rand(0, 0xFFFFFFFF);

            $mtRandDiffBits[] = CompareRNGPerformanceCommand::NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $mtRandTime = microtime(true);

        for($i = 0; $i < self::ITERATIONS; $i++)
            mt_rand(0, 9000000); // NOT a power of 2, since that's unlikely IRL, but MAY perform better

        $mtRandTime = microtime(true) - $mtRandTime;

        return [ $mtRandDiffBits, $mtRandTime ];
    }

    public function testRandomInt(): array
    {
        $randomIntDiffBits = [];

        $previous = random_int(0, 0xFFFFFFFF); // to test bit distribution, we must ask for bits in even distribution

        for($i = 0; $i < self::ITERATIONS; $i++)
        {
            $r = random_int(0, 0xFFFFFFFF);

            $randomIntDiffBits[] = CompareRNGPerformanceCommand::NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $randomIntTime = microtime(true);

        for($i = 0; $i < self::ITERATIONS; $i++)
            random_int(0, 9000000); // NOT a power of 2, since that's unlikely IRL, but MAY perform better

        $randomIntTime = microtime(true) - $randomIntTime;

        return [ $randomIntDiffBits, $randomIntTime ];
    }

    public function testSquirrel3(): array
    {
        $squirrel3 = new Squirrel3();
        $squirrel3DiffBits = [];

        $previous = $squirrel3->rngNextInt(0, 0xFFFFFFFF); // to test bit distribution, we must ask for bits in even distribution

        for($i = 0; $i < self::ITERATIONS; $i++)
        {
            $r = $squirrel3->rngNextInt(0, 0xFFFFFFFF);

            $squirrel3DiffBits[] = CompareRNGPerformanceCommand::NumberOfSetBits($r ^ $previous) / 32;

            $previous = $r;
        }

        $squirrel3Time = microtime(true);

        for($i = 0; $i < self::ITERATIONS; $i++)
            $squirrel3->rngNextInt(0, 9000000); // NOT a power of 2, since that's unlikely IRL, but MAY perform better

        $squirrel3Time = microtime(true) - $squirrel3Time;

        return [ $squirrel3DiffBits, $squirrel3Time ];
    }
}
