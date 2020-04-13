<?php
namespace App\Command;

use App\Service\CalendarService;
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
            ->setDescription('Runs text through the profanity filter, and displays the result.')
            ->addArgument('date', InputArgument::REQUIRED, 'Date to test, in "Y-m-d" format.')
        ;
    }

    protected function doCommand()
    {
        $dateString = $this->input->getArgument('date');

        $dateToTest = \DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        $this->calendarService->setToday($dateToTest);

        echo $dateToTest->format('Y-m-d') . ' ' . ($this->calendarService->isEaster() ? 'IS Easter' : 'is NOT Easter') . "\n";
    }
}
