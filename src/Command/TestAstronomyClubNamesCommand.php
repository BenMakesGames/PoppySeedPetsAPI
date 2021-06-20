<?php
namespace App\Command;

use App\Service\PetActivity\Group\AstronomyClubService;
use Symfony\Component\Console\Command\Command;

class TestAstronomyClubNamesCommand extends PoppySeedPetsCommand
{
    private $astronomyClubService;

    public function __construct(AstronomyClubService $astronomyClubService)
    {
        $this->astronomyClubService = $astronomyClubService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:test-astronomy-club-names')
            ->setDescription('Tests the astronomy club name generator/algorithm.')
        ;
    }

    protected function doCommand(): int
    {
        for($i = 0; $i < 20; $i++)
            $this->output->writeln($this->astronomyClubService->generateGroupName());

        return Command::SUCCESS;
    }
}
