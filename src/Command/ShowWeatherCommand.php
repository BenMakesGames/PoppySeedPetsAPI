<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


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

        $weather = WeatherService::getWeather($time);

        var_export($weather);

        return self::SUCCESS;
    }
}
