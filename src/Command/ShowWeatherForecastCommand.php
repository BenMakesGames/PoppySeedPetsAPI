<?php
namespace App\Command;

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
            ->addArgument('time', InputArgument::OPTIONAL, 'DateTime string to show weather for; leave blank for current time.')
            ->addOption('hours', 'r', InputOption::VALUE_REQUIRED, 'Number of hours to forecast.')
            ->setDescription('Show a weather forecast.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time = $input->getArgument('time');
        $hours = (int)$input->getOption('hours');

        if(!$time)
            $time = new \DateTimeImmutable();
        else
            $time = new \DateTimeImmutable($time);

        $time = $time->setTime($time->format('G'), 0, 0);

        $output->writeln("time,\ttemp,\tclouds,\train");

        for($hour = 0; $hour < $hours; $hour++)
        {
            $timeToConsider = $time->modify('+' . $hour . ' hours');
            $weather = $this->weatherService->getWeather($timeToConsider, null);
            $output->writeln(
                "{$this->weatherService->getHourSince2000($timeToConsider)},\t{$weather->getTemperature()},\t{$weather->getClouds()},\t{$weather->getRainfall()}"
            );
        }

        return Command::SUCCESS;
    }
}
