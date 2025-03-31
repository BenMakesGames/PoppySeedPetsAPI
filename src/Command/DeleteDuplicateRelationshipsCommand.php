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

use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteDuplicateRelationshipsCommand extends Command
{
    private EntityManagerInterface $em;
    private PetRelationshipRepository $petRelationshipRepository;

    public function __construct(EntityManagerInterface $em, PetRelationshipRepository $petRelationshipRepository)
    {
        $this->em = $em;
        $this->petRelationshipRepository = $petRelationshipRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:delete-duplicate-relationships')
            ->setDescription('Deletes duplicate relationships.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statement = $this->em->getConnection()->prepare('SELECT pet_id,relationship_id,COUNT(id) AS qty FROM `pet_relationship` GROUP BY CONCAT(pet_id, \':\', relationship_id) HAVING qty > 1');
        $results = $statement->executeQuery()->fetchAllAssociative();

        if(count($results) === 0)
        {
            echo 'No duplicate relationships were found.';
            return self::SUCCESS;
        }

        $deleting = 0;

        foreach($results as $result)
        {
            $relationships = $this->petRelationshipRepository->findBy([
                'pet' => $result['pet_id'],
                'relationship' => $result['relationship_id']
            ]);

            echo "\n";
            echo $relationships[0]->getPet()->getName() . ' (#' . $result['pet_id'] . ') knows ' . $relationships[0]->getRelationship()->getName() . ' (#' . $result['relationship_id'] . ')' . "\n\n";

            for($i = 0; $i < count($relationships); $i++)
            {
                $r = $relationships[$i];

                echo '#' . ($i + 1) . "\n";
                echo '  ' . $r->getMetOn()->format('Y-m-d H:i:s') . ': ' . $r->getMetDescription() . "\n";
                echo '  Currently: ' . $r->getCurrentRelationship() . "\n";
                echo '  Goal: ' . $r->getRelationshipGoal() . "\n";

                echo "\n";
            }

            do
            {
                $keep = (int)readline('Which one should be kept? ');
            } while($keep < 1 || $keep > count($relationships));

            echo "\n";

            for($i = 0; $i < count($relationships); $i++)
            {
                if($i === $keep - 1) continue;

                $r = $relationships[$i];

                echo '"' . $r->getMetOn()->format('Y-m-d H:i:s') . ': ' . $r->getMetDescription() . '" will be deleted.' . "\n";
                $this->em->remove($r);
                $deleting++;
            }
        }

        echo "\n";
        echo 'Alright! Now\'s your final chance to press CTRL+C before deleting those ' . $deleting . ' relationships.' . "\n";
        readline();

        echo 'Committing changes... ';
        $this->em->flush();
        echo 'done!' . "\n";

        return self::SUCCESS;
    }
}
