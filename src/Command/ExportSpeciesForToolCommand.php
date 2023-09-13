<?php
namespace App\Command;

use App\Entity\PetSpecies;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportSpeciesForToolCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:export-species-for-tool')
            ->setDescription('Export species data in a format appropriate for copy-pasting into the Poppy Seed Pets Tools project.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $species = $this->em->getRepository(PetSpecies::class)->findAll();

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

        return 0;
    }
}
