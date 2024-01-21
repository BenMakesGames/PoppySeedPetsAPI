<?php
namespace App\Command;

use Symfony\Component\Console\Input\InputArgument;

class TestChineseCalendar extends PoppySeedPetsCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:test-chinese-calendar')
            ->setDescription('Shows output of Chinese calendar "solar" method.')
            ->addArgument('date', InputArgument::REQUIRED, 'Gregorian date to test, in "Y-m-d" format.')
        ;
    }

    protected function doCommand(): int
    {
        [$year, $month, $day] = explode('-', $this->input->getArgument('date'));

        $chineseCalendar = new \Overtrue\ChineseCalendar\Calendar();

        var_export($chineseCalendar->solar($year, $month, $day));

        return self::SUCCESS;
    }
}
