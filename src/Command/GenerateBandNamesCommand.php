<?php
namespace App\Command;

use App\Service\PetActivity\Group\BandService;
use Symfony\Component\Console\Command\Command;

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
