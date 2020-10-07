<?php
namespace App\Command;

use App\Entity\PetSpecies;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetSpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportSpeciesForToolCommand extends Command
{
    private $em;
    private $petSpeciesRepository;

    public function __construct(EntityManagerInterface $em, PetSpeciesRepository $petSpeciesRepository)
    {
        $this->em = $em;
        $this->petSpeciesRepository = $petSpeciesRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:export-species-for-tool')
            ->setDescription('Export species data in a format appropriate for copy-pasting into the Poppy Seed Pets Tools project.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $species = $this->petSpeciesRepository->findAll();

        $pets = array_map(function(PetSpecies $species) {
            return [
                'image' => $species->getImage(),
                'flipX' => $species->getFlipX(),
                'handX' => $species->getHandX(),
                'handY' => $species->getHandY(),
                'handAngle' => $species->getHandAngle(),
                'handBehind' => $species->getHandBehind(),
                'hatX' => $species->getHatX(),
                'hatY' => $species->getHatY(),
                'hatAngle' => $species->getHatAngle()
            ];
        }, $species);

        echo \GuzzleHttp\json_encode($pets);
    }
}
