<?php
namespace App\Command;

use App\Service\CalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class TestEasterCommand extends PoppySeedPetsCommand
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
            ->setName('app:test-easter')
            ->setDescription('Tests whether or not a given date is "Easter".')
            ->addArgument('date', InputArgument::REQUIRED, 'Date to test, in "Y-m-d" format.')
        ;
    }

    protected function doCommand(): int
    {
        $dateString = $this->input->getArgument('date');

        $dateToTest = \DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
        $dateToTest = $dateToTest->setTime(0, 0, 0);

        $this->calendarService->setToday($dateToTest);

        echo $dateToTest->format('Y-m-d') . ' ' . ($this->calendarService->isEaster() ? 'IS Easter' : 'is NOT Easter') . "\n";

        return Command::SUCCESS;
    }
}
