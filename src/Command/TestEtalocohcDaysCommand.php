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

use App\Service\TraderService;

class TestEtalocohcDaysCommand extends PoppySeedPetsCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:test-etalocohc-days')
            ->setDescription('Shows upcoming Etalocŏhc days.')
        ;
    }

    protected function doCommand(): int
    {
        $day = new \DateTimeImmutable();

        for($i = 0; $i < 365; $i++)
        {
            if(TraderService::isEtalocohcDay($day))
            {
                $cost = TraderService::getEtalocohcCost($day);
                $this->output->writeln("√ {$day->format('Y-m-d')} - costs $cost");
            }
            else
                $this->output->writeln("x {$day->format('Y-m-d')}");

            $day = $day->modify('+1 day');
        }

        return self::SUCCESS;
    }
}
