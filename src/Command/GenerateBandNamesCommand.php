<?php
namespace App\Command;

use App\Repository\UserRepository;
use App\Service\JsonLogicParserService;
use App\Service\PetActivity\Group\BandService;
use App\Service\PetGroupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class GenerateBandNamesCommand extends PoppySeedPetsCommand
{
    private $bandService;

    public function __construct(BandService $bandService)
    {
        $this->bandService = $bandService;

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
            echo $this->bandService->generateBandName() . "\n";

        return Command::SUCCESS;
    }
}
