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

class TestRelationshipMigrationCommand extends Command
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
            ->setName('app:test-relationship-migration')
            ->setDescription('For testing relationship migration logic')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $relationships = $this->petRelationshipRepository->findAll();

        foreach($relationships as $relationship)
        {
            $otherSide = $relationship->getRelationship()->getRelationshipWith($relationship->getPet());

            // each value will range from 0 to 2000
            $intimacy = $relationship->getIntimacy() + $otherSide->getIntimacy();
            $passion = $relationship->getPassion() + $otherSide->getPassion();
            $commitment = $relationship->getCommitment() + $otherSide->getCommitment();

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

            if($relationship->getIntimacy() >= 333)
            {
                $possibleGoals[] = RelationshipEnum::FRIEND;

                if($relationship->getCommitment() >= 333)
                {
                    $possibleGoals[] = RelationshipEnum::BFF;
                    $possibleGoals[] = RelationshipEnum::MATE;
                }

                if($relationship->getPassion() >= 333)
                {
                    $possibleGoals[] = RelationshipEnum::MATE;
                    $possibleGoals[] = RelationshipEnum::FWB;
                }
            }
            else
            {
                $possibleGoals[] = RelationshipEnum::FRIENDLY_RIVAL;

                if($relationship->getCommitment() >= 333)
                    $possibleGoals[] = RelationshipEnum::FRIEND;

                if($relationship->getCommitment() >= 666)
                {
                    $possibleGoals[] = RelationshipEnum::BFF;
                    $possibleGoals[] = RelationshipEnum::MATE;
                }

                if($relationship->getPassion() >= 333)
                    $possibleGoals[] = RelationshipEnum::FWB;
            }

            $relationshipGoal = ArrayFunctions::pick_one($possibleGoals);
            
        }
    }
}
