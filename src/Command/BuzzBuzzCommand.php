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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuzzBuzzCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:buzz-buzz')
            ->setDescription('Progresses beehives.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // population increase
        $this->em->getConnection()->executeQuery('
            UPDATE beehive
            SET
                workers = workers + 1,
                flower_power = flower_power - 5
            WHERE workers < 1000000 AND flower_power >= 20
        ');

        // flower power
        $this->em->getConnection()->executeQuery('
            UPDATE beehive
            SET
                royal_jelly_progress = royal_jelly_progress + LOG(workers) * 2,
                honeycomb_progress = honeycomb_progress + LOG(workers) * 3,
                honeycomb_progress = honeycomb_progress + LOG(workers) * 4,
                misc_progress = misc_progress + LOG(workers) * 10,
                flower_power = flower_power - LOG(workers) / 5
            WHERE
                flower_power >= LOG(workers)
        ');

        return self::SUCCESS;
    }
}
