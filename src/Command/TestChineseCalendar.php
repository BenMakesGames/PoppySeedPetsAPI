<?php
namespace App\Command;

use App\Service\CalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class TestChineseCalendar extends PoppySeedPetsCommand
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:test-chinese-calendar')
            ->setDescription('Shows output of Chinese calendar "solar" method.')
            ->addArgument('date', InputArgument::REQUIRED, 'Gregorian date to test, in "Y-m-d" format.')
        ;
    }

    protected function doCommand(): int
    {
        list($year, $month, $day) = explode('-', $this->input->getArgument('date'));

        $chineseCalendar = new \Overtrue\ChineseCalendar\Calendar();

        var_export($chineseCalendar->solar($year, $month, $day));

        return Command::SUCCESS;
    }
}
