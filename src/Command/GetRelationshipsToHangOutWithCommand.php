<?php

namespace App\Command;

use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetRelationshipsToHangOutWithCommand extends Command
{
    private PetRelationshipRepository $petRelationshipRepository;
    private PetRepository $petRepository;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, PetRepository $petRepository
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->petRepository = $petRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:get-relationships-to-hang-out-with')
            ->setDescription('Finds all the pets a given pet would consider hanging out with.')
            ->addArgument('pet', InputArgument::REQUIRED, 'ID of pet to check.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $petId = (int)$input->getArgument('pet');

        if($petId <= 0)
            throw new \InvalidArgumentException('pet must be an ID (greater than 0).');

        $pet = $this->petRepository->find($petId);

        if(!$pet)
            throw new \InvalidArgumentException('pet #' . $petId . ' does not exist.');

        $output->writeln($pet->getName() . ' (#' . $petId . ')');

        $time = microtime(true);
        $relationships = $this->petRelationshipRepository->getRelationshipsToHangOutWith($pet);
        $time = microtime(true) - $time;

        $output->writeln('Found ' . count($relationships) . ' in ' . round($time, 2) . 's:');

        foreach($relationships as $r)
        {
            $friend = $r->getRelationship();

            $output->writeln('* ' . $friend->getName() . ' (#' . $friend->getId() . ')');
        }

        return Command::SUCCESS;
    }
}
