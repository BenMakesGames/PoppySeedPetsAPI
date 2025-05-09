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

use Symfony\Component\Console\Input\InputArgument;

class TestChineseCalendar extends PoppySeedPetsCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:test-chinese-calendar')
            ->setDescription('Shows output of Chinese calendar "solar" method.')
            ->addArgument('date', InputArgument::REQUIRED, 'Gregorian date to test, in "Y-m-d" format.')
        ;
    }

    protected function doCommand(): int
    {
        [$year, $month, $day] = explode('-', $this->input->getArgument('date'));

        $chineseCalendar = new \Overtrue\ChineseCalendar\Calendar();

        var_export($chineseCalendar->solar((int)$year, (int)$month, (int)$day));

        return self::SUCCESS;
    }
}
