<?php

namespace App\Command;

use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Functions\StringFunctions;
use App\Repository\PetRelationshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RelationshipMigrationCommand extends Command
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
            ->setName('app:relationship-migration')
            ->setDescription('Migrates relationships to new system.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tallies = [];
        $relationships = $this->petRelationshipRepository->findAll();

        foreach($relationships as $relationship)
        {
            $otherSide = $relationship->getRelationship()->getRelationshipWith($relationship->getPet());

            if($otherSide === null)
            {
                $otherSide = (new PetRelationship())
                    ->setPet($relationship->getRelationship())
                    ->setRelationship($relationship->getPet())
                    ->setOldTriangleStats(
                        mt_rand(ceil($relationship->getOldIntimacy() * 3 / 4), $relationship->getOldIntimacy()),
                        $relationship->getRelationship()->getOldWouldBang($relationship->getPet()) ? 750 : 250,
                        mt_rand(ceil($relationship->getOldCommitment() / 2), $relationship->getOldCommitment())
                    )
                ;

                $this->em->persist($otherSide);
            }

            // each value will range from 0 to 2000
            $intimacy = $relationship->getOldIntimacy() + $otherSide->getOldIntimacy();
            $passion = $relationship->getOldPassion() + $otherSide->getOldPassion();
            $commitment = $relationship->getOldCommitment() + $otherSide->getOldCommitment();

            if($passion >= 1000)
            {
                if($intimacy >= 500)
                {
                    if($commitment < 1000)
                        $currentRelationship = RelationshipEnum::FWB;
                    else
                        $currentRelationship = RelationshipEnum::MATE;
                }
                else
                    $currentRelationship = RelationshipEnum::FRIEND;
            }
            else
            {
                if($intimacy >= 1000)
                {
                    if($commitment >= 1000)
                        $currentRelationship = RelationshipEnum::BFF;
                    else
                        $currentRelationship = RelationshipEnum::FRIEND;
                }
                else if($intimacy >= 500)
                    $currentRelationship = RelationshipEnum::FRIEND;
                else
                    $currentRelationship = RelationshipEnum::FRIENDLY_RIVAL;
            }

            $possibleGoals = [];

            if($relationship->getPet()->getOldWouldBang($otherSide->getPet()))
            {
                $possibleGoals[] = RelationshipEnum::FWB;
                $possibleGoals[] = RelationshipEnum::MATE;

                if($relationship->getOldCommitment() + $relationship->getOldPassion() + $relationship->getOldIntimacy() >= 1000)
                    $possibleGoals[] = RelationshipEnum::MATE;
            }
            else
            {
                $possibleGoals[] = RelationshipEnum::FRIEND;

                if($relationship->getOldIntimacy() >= 333)
                {
                    $possibleGoals[] = RelationshipEnum::BFF;

                    if($relationship->getOldCommitment() >= 333)
                    {
                        $possibleGoals[] = RelationshipEnum::BFF;
                        $possibleGoals[] = RelationshipEnum::MATE;
                    }

                    if($relationship->getOldPassion() >= 333)
                    {
                        $possibleGoals[] = RelationshipEnum::MATE;
                        $possibleGoals[] = RelationshipEnum::FWB;
                    }
                }
                else
                {
                    if($relationship->getOldCommitment() >= 333)
                        $possibleGoals[] = RelationshipEnum::BFF;

                    if($relationship->getOldCommitment() >= 666)
                    {
                        $possibleGoals[] = RelationshipEnum::BFF;
                        $possibleGoals[] = RelationshipEnum::MATE;
                    }

                    if($relationship->getOldPassion() >= 333)
                        $possibleGoals[] = RelationshipEnum::FWB;
                }


                if(count($possibleGoals) <= 1 && mt_rand(1, 4) === 1)
                    $possibleGoals[] = RelationshipEnum::FRIENDLY_RIVAL;
            }

            $relationshipGoal = ArrayFunctions::pick_one($possibleGoals);

            $output->writeln($relationship->getPet()->getName() . ' #' . $relationship->getPet()->getId() . ' + ' . $otherSide->getPet()->getName() . ' #' . $otherSide->getPet()->getId());
            $output->writeln('Intimacy/Passion/Commitment = ' . $relationship->getOldIntimacy() . '/' . $relationship->getOldPassion() . '/' . $relationship->getOldCommitment() . '; total of ' . $intimacy . '/' . $passion . '/' . $commitment);
            $output->writeln('Relationship status = ' . $currentRelationship . ', aiming for ' . $relationshipGoal);
            $output->writeln('');

            $key = $currentRelationship . ', aiming for ' . $relationshipGoal;

            if(array_key_exists($key, $tallies))
                $tallies[$key]++;
            else
                $tallies[$key] = 1;

            $relationship
                ->setCurrentRelationship($currentRelationship)
                ->setRelationshipGoal($relationshipGoal);
            ;

            $otherSide->setCurrentRelationship($currentRelationship);
        }

        $output->writeln(count($relationships) . ' total relationships!');
        foreach($tallies as $key=>$tally)
            $output->writeln($key . ' = ' . $tally . ' (' . round($tally * 100 / count($relationships), 1) . '%)');

        $output->writeln('');
        $output->writeln('Writing to DB...');

        $this->em->flush();

        $output->writeln('Done!');
    }
}
