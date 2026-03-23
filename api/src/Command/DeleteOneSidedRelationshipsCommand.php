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

class DeleteOneSidedRelationshipsCommand extends Command
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
            ->setName('app:delete-one-sided-relationships')
            ->setDescription('Finds relationships where pet A knows pet B, but pet B does not know pet A, and deletes the relationship.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo 'Querying... (this can take a while...)' . PHP_EOL;

        $statement = $this->em->getConnection()->prepare('
            SELECT pr1.id
            FROM pet_relationship pr1
            LEFT JOIN pet_relationship pr2 
                ON pr1.pet_id = pr2.relationship_id 
                AND pr1.relationship_id = pr2.pet_id
            WHERE pr2.id IS NULL;
        ');
        $results = $statement->executeQuery()->fetchAllAssociative();

        echo 'Found ' . count($results) . ' one-sided relationships.' . PHP_EOL;

        if(count($results) === 0)
            return 0;

        echo 'Press ENTER to continue, or CTRL+C to abort.';
        readline();

        $statement = $this->em->getConnection()->prepare('DELETE FROM pet_relationship WHERE id = :id');
        foreach($results as $row) {
            $statement->executeStatement(['id' => $row['id']]);
            echo 'Deleted relationship with ID ' . $row['id'] . PHP_EOL;
        }

        echo 'Done!' . PHP_EOL;

        return 0;
    }
}
