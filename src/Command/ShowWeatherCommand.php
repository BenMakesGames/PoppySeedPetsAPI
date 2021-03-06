<?php
namespace App\Command;

use App\Functions\RandomFunctions;
use App\Service\WeatherService;
use Symfony\Component\Console\Command\Command;
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
            ->setDescription('Show the weather.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $monthTotal = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $csv = fopen('out.csv', 'w');

        for($hourOfYear = 0; $hourOfYear < 8760; $hourOfYear++)
        {
            $rain = $this->weatherService->getRainfall($hourOfYear);
            $temp = $this->weatherService->getTemperature($hourOfYear, $rain);

            fwrite($csv, $rain . ',' . $temp . "\n");

            $month = (int)($hourOfYear / 730);
            $monthTotal[$month] += $rain;
        }

        fclose($csv);

        var_export($monthTotal);

        echo array_sum($monthTotal);

        return Command::SUCCESS;
    }
}
