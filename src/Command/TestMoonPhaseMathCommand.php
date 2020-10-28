<?php
namespace App\Command;

use App\Functions\DateFunctions;
use Symfony\Component\Console\Command\Command;

class TestMoonPhaseMathCommand extends PoppySeedPetsCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:test-moon-phase-math')
            ->setDescription('Tests horrible, horrible Moon phase math.')
        ;
    }

    protected function doCommand(): int
    {
        $currentYear = (int)(new \DateTimeImmutable())->format('Y');

        for($year = $currentYear - 1; $year <= $currentYear + 1; $year++)
        {
            for($month = 1; $month <= 12; $month++)
            {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                for($day = 1; $day <= $daysInMonth; $day++)
                {
                    $date = DateFunctions::createFromYearMonthDay($year, $month, $day);
                    $fullMoonName = DateFunctions::getFullMoonName($date);

                    if($fullMoonName)
                    {
                        $exact = DateFunctions::getIsExactFullMoon($date) ? ' *' : '';
                        $moonAge = DateFunctions::getMoonAge($date);
                        echo $date->format('Y-m-d') . ' ' . round($moonAge, 3) . ' ' . $fullMoonName . $exact . "\n";
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
