<?php
namespace App\Command;

use App\Entity\Pet;
use App\Repository\PetRepository;
use App\Service\PetGroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindPetsUnhappyInAGroupCommand extends Command
{
    private $petRepository;
    private $petGroupService;

    public function __construct(PetRepository $petRepository, PetGroupService $petGroupService)
    {
        $this->petRepository = $petRepository;
        $this->petGroupService = $petGroupService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:find-pets-unhappy-in-a-group')
            ->setDescription('Find pets that are unhappy in at least one of their groups.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Pet[] $pets */
        $pets = $this->petRepository->createQueryBuilder('p')
            ->join('p.groups', 'g')
            ->andWhere('g IS NOT NULL')
            ->getQuery()
            ->execute()
        ;

        $results = [];

        foreach($pets as $pet)
        {
            foreach($pet->getGroups() as $group)
            {
                $happiness = $this->petGroupService->getMemberHappiness($group, $pet);
                if($happiness < 0)
                {
                    $results[] = [
                        'pet' => $pet->getId(),
                        'owner' => $pet->getOwner()->getId(),
                        'group' => $group->getId(),
                        'happiness' => $happiness
                    ];
                }
            }
        }

        echo \GuzzleHttp\json_encode($results, JSON_PRETTY_PRINT);

        return 0;
    }
}
