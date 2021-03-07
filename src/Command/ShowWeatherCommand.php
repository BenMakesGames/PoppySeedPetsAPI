<?php
namespace App\Command;

use App\Functions\RandomFunctions;
use App\Service\WeatherService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowWeatherCommand extends Command
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
            ->setName('app:show-weather')
            ->addArgument('time', InputArgument::OPTIONAL, 'DateTime string to show weather for; leave blank for current time.')
            ->setDescription('Show the weather.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time = $input->getArgument('time');

        if(!$time)
            $time = new \DateTimeImmutable();
        else
            $time = new \DateTimeImmutable($time);

        $weather = $this->weatherService->getWeather($time, null);

        var_export($weather);

        return Command::SUCCESS;
    }
}
