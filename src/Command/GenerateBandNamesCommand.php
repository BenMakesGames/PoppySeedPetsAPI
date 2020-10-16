<?php
namespace App\Command;

use App\Repository\UserRepository;
use App\Service\JsonLogicParserService;
use App\Service\PetGroupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class GenerateBandNamesCommand extends PoppySeedPetsCommand
{
    private $petGroupService;

    public function __construct(PetGroupService $petGroupService)
    {
        $this->petGroupService = $petGroupService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:generate-band-names')
            ->setDescription('Generates 20 random band names.')
        ;
    }

    protected function doCommand(): int
    {
        for($x = 0; $x < 20; $x++)
            echo $this->petGroupService->generateBandName() . "\n";

        return Command::SUCCESS;
    }
}
