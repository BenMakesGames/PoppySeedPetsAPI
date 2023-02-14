<?php
namespace App\Command;

use App\Entity\Pet;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\PetGroupService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignActivityPersonalitiesCommand extends Command
{
    public const BATCH_SIZE = 200;

    private EntityManagerInterface $em;
    private PetRepository $petRepository;
    private IRandom $rng;

    public function __construct(PetRepository $petRepository, EntityManagerInterface $em, Squirrel3 $squirrel3)
    {
        $this->petRepository = $petRepository;
        $this->em = $em;
        $this->rng = $squirrel3;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:assign-activity-personalities')
            ->setDescription('Assign activity personalities to pets that don\'t have one.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        /** @var Pet[] $pets */
        $pets = $this->getPetsWithoutActivityPersonalities();

        if(count($pets) === 0)
        {
            echo 'No pets to update.' . "\n";
            return 0;
        }

        while(count($pets) > 0)
        {
            foreach($pets as $pet)
                $pet->assignActivityPersonality($this->rng);

            $this->em->flush();
            $this->em->clear();

            echo 'Updated ' . count($pets) . ' pets.' . "\n";

            $pets = $this->getPetsWithoutActivityPersonalities();
        }

        return 0;
    }

    private function getPetsWithoutActivityPersonalities(): array
    {
        return $this->petRepository->findBy([ 'activityPersonality' => 0 ], null, self::BATCH_SIZE);
    }

}
