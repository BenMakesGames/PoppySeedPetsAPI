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

class IncreaseTimeCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:increase-time')
            ->setDescription('Increases Time of all Pets by 1, to a maximum of 2880 minutes (48 hours).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // pet logic...
        $this->em->getConnection()->executeQuery('
            START TRANSACTION;
            UPDATE pet_house_time LEFT JOIN pet ON pet_id=pet.id SET `activity_time` = `activity_time` + 1 WHERE location = \'home\' AND `activity_time` < 2880;
            UPDATE pet_house_time SET `social_energy` = `social_energy` + 1 WHERE `social_energy` < 2880;
            COMMIT;
        ');

        // pet group logic...
        $this->em->getConnection()->executeQuery('
            START TRANSACTION;
            UPDATE pet_group SET `social_energy` = `social_energy` + 1 WHERE `social_energy` < 2880;
            COMMIT;
        ');

        if(!array_key_exists('APP_MAINTENANCE', $_ENV) || !$_ENV['APP_MAINTENANCE'])
        {
            // fireplace logic...
            $this->em->getConnection()->executeQuery('
                START TRANSACTION;
                UPDATE fireplace SET longest_streak = current_streak + 1 WHERE current_streak >= longest_streak;
                UPDATE fireplace SET heat = heat - 1, current_streak = current_streak + 1, points = points + 1 WHERE heat > 1;
                UPDATE fireplace SET alcohol = alcohol - 1, gnome_points = gnome_points + 1 WHERE alcohol > 0;
                UPDATE fireplace SET heat = 0, alcohol = 0, current_streak = 0, points = points + 1 WHERE heat = 1;
                COMMIT;
            ');
        }

        // delete expired sessions...
        $this->em->getConnection()->executeQuery(
            'START TRANSACTION; DELETE FROM user_session WHERE session_expiration<:now; COMMIT;',
            [ 'now' => (new \DateTimeImmutable())->format('Y-m-d H:i:s') ]
        );

        // delete old device stats...
        $this->em->getConnection()->executeQuery(
            'START TRANSACTION; DELETE FROM device_stats WHERE time<:oneMonthAgo; COMMIT;',
            [ 'oneMonthAgo' => (new \DateTimeImmutable())->modify('-1 month')->format('Y-m-d') ]
        );

        return self::SUCCESS;
    }
}
