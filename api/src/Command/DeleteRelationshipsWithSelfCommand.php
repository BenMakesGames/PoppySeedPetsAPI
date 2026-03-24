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

use App\Entity\PetRelationship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteRelationshipsWithSelfCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:delete-relationships-with-self')
            ->setDescription('Finds relationships where pet A knows itself.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo 'Working on it...' . PHP_EOL;

        $statement = $this->em->getConnection()->prepare('
            DELETE FROM pet_relationship
            WHERE pet_id = relationship_id;
        ');

        $statement->executeQuery();

        echo 'Done!' . PHP_EOL;

        return 0;
    }
}
