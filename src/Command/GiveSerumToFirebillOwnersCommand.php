<?php
namespace App\Command;

use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GiveSerumToFirebillOwnersCommand extends Command
{
    private $em;
    private $petRepository;
    private $inventoryService;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, InventoryService $inventoryService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->inventoryService = $inventoryService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:give-serum-to-firebill-owners')
            ->setDescription('Gives all players with a Firebill a Species Transmigration Serum.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pets = $this->petRepository->findBy([ 'species' => 12 ]);

        foreach($pets as $pet)
            $this->inventoryService->receiveItem('Species Transmigration Serum', $pet->getOwner(), null, 'You have no idea where or how, but it seems ' . $pet->getName() . ' found this and brought it home...', LocationEnum::HOME, true);

        $this->em->flush();

        $output->writeln('Gave out ' . count($pets) . ' Species Transmigration Serums');
    }
}
