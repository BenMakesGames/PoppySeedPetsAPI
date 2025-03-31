<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


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

    protected function configure(): void
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
