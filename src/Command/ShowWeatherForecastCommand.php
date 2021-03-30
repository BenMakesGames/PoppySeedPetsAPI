<?php
namespace App\Command;

use App\Functions\ArrayFunctions;
use App\Functions\RandomFunctions;
use App\Service\WeatherService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowWeatherForecastCommand extends Command
{
    private $weatherService;

    public function __construct(WeatherService  $weatherService)
    {
        parent::__construct();

        $this->weatherService = $weatherService;
    }

    protected function configure()
    {
        $this
            ->setName('app:show-weather-forecast')
            ->addArgument('date', InputArgument::OPTIONAL, 'DateTime string to show weather for; leave blank for current date.')
            ->setDescription('Show a weather forecast.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');

        if(!$date)
            $date = new \DateTimeImmutable();
        else
            $date = new \DateTimeImmutable($date);

        $date = $date->setTime(0, 0, 0);

        for($day = 1; $day <= 7; $day++)
        {
            $dateToConsider = $date->modify('+' . $day . ' days');
            $weather = $this->weatherService->computeWeatherForecast($dateToConsider);
            echo $weather->date->format('Y-m-d H:i:s') . "\n";
            echo "-------------------\n";
            echo "Holidays: " . ArrayFunctions::list_nice($weather->holidays) . "\n";
            echo "\n";
        }

        return Command::SUCCESS;
    }
}
