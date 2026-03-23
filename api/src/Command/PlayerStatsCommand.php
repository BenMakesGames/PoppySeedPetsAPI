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
use Doctrine\ORM\Query;

class PlayerStatsCommand extends PoppySeedPetsCommand
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:player-stats')
            ->setDescription('Gives some stats about players.')
        ;
    }

    protected function doCommand(): int
    {
        $now = new \DateTimeImmutable();

        $activityQuery = $this->em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User AS u WHERE u.lastActivity>=?0');
        $newUsersQuery = $this->em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User AS u WHERE u.registeredOn>=?0');

        $data = [
            'Activity in the Last 1 Day' => $this->getCount($activityQuery, $now->modify('-24 hours')->format('Y-m-d H:i:s')),
            'Activity in the Last 2 Days' => $this->getCount($activityQuery, $now->modify('-48 hours')->format('Y-m-d H:i:s')),
            'Activity in the Last 3 Days' => $this->getCount($activityQuery, $now->modify('-72 hours')->format('Y-m-d H:i:s')),
            'Activity in the last 5 Days' => $this->getCount($activityQuery, $now->modify('-5 days')->format('Y-m-d H:i:s')),
            'Activity in the last 1 Week' => $this->getCount($activityQuery, $now->modify('-7 days')->format('Y-m-d H:i:s')),
            'Activity in the last 2 Weeks' => $this->getCount($activityQuery, $now->modify('-14 days')->format('Y-m-d H:i:s')),

            'New Users in the Last 1 Day' => $this->getCount($newUsersQuery, $now->modify('-24 hours')->format('Y-m-d H:i:s')),
            'New Users in the Last 2 Days' => $this->getCount($newUsersQuery, $now->modify('-48 hours')->format('Y-m-d H:i:s')),
            'New Users in the Last 3 Days' => $this->getCount($newUsersQuery, $now->modify('-72 hours')->format('Y-m-d H:i:s')),
            'New Users in the last 5 Days' => $this->getCount($newUsersQuery, $now->modify('-5 days')->format('Y-m-d H:i:s')),
            'New Users in the last 1 Week' => $this->getCount($newUsersQuery, $now->modify('-7 days')->format('Y-m-d H:i:s')),
            'New Users in the last 2 Weeks' => $this->getCount($newUsersQuery, $now->modify('-14 days')->format('Y-m-d H:i:s')),
        ];

        echo json_encode($data, JSON_PRETTY_PRINT);

        return self::SUCCESS;
    }

    private function getCount(Query $query, string $argument): int
    {
        return (int)$query->execute([ $argument ])[0][1];
    }
}
