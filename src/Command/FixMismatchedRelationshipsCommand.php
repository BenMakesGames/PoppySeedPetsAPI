<?php
namespace App\Command;

use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixMismatchedRelationshipsCommand extends Command
{
    private $em;
    private $petRelationshipRepository;

    public function __construct(EntityManagerInterface $em, PetRelationshipRepository $petRelationshipRepository)
    {
        $this->em = $em;
        $this->petRelationshipRepository = $petRelationshipRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:fix-mismatched-relationships')
            ->setDescription('Finds mismatched relationships (relationships where each side has a different relationship status), and presents UI for fixing them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statement = $this->em->getConnection()->prepare('
            SELECT *
            FROM pet_relationship AS pr1
            LEFT JOIN pet_relationship AS pr2 ON pr1.pet_id=pr2.relationship_id AND pr1.relationship_id=pr2.pet_id
            WHERE pr1.current_relationship!=pr2.current_relationship
        ');
        $statement->execute();
        $results = $statement->fetchAll(\PDO::FETCH_NUM);

        if(count($results) === 0)
        {
            echo 'No mismatched relationships were found.';
            return;
        }

        $completed = [];

        $updating = 0;

        foreach($results as $result)
        {
            $uniqueKey = min($result[0], $result[8]) . '/' . max($result[0], $result[8]);

            if(in_array($uniqueKey, $completed)) continue;

            $completed[] = $uniqueKey;

            $relationships = $this->petRelationshipRepository->findBy([
                'id' => [ $result[0], $result[8] ],
            ]);

            echo "\n";
            echo $relationships[0]->getPet()->getName() . ' (#' . $relationships[0]->getPet()->getId() . ') knows ' . $relationships[1]->getPet()->getName() . ' (#' . $relationships[1]->getPet()->getId() . ')' . "\n\n";

            for($i = 0; $i < 2; $i++)
            {
                $r = $relationships[$i];

                echo '#' . ($i + 1) . "\n";
                echo '  ' . $r->getPet()->getName() . ': ' . $r->getMetDescription() . "\n";
                echo '  Currently: ' . $r->getCurrentRelationship() . "\n";
                echo '  Goal: ' . $r->getRelationshipGoal() . "\n";

                echo "\n";
            }

            do
            {
                $keep = (int)readline('Which one has the correct current relationship? ');
            } while($keep < 1 || $keep > 2);

            echo "\n";

            $correctRelationship = $relationships[$keep - 1];
            $wrongRelationship = $relationships[2 - $keep];

            $wrongRelationship->setCurrentRelationship($correctRelationship->getCurrentRelationship());

            echo '"' . $wrongRelationship->getPet()->getName() . ': ' . $wrongRelationship->getMetDescription() . '" will be set to "' . $wrongRelationship->getCurrentRelationship() . '".' . "\n";

            $updating++;
        }

        echo "\n";
        echo 'Alright! Now\'s your final chance to press CTRL+C before updating those ' . $updating . ' relationships.' . "\n";
        readline();

        echo 'Committing changes... ';
        $this->em->flush();
        echo 'done!' . "\n";
    }
}
