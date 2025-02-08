<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\WeatherService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowWeatherCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
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

        $weather = WeatherService::getWeather($time, null);

        var_export($weather);

        return self::SUCCESS;
    }
}
