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

use App\Repository\PetRelationshipRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportRelationshipNetworkCommand extends Command
{
    private PetRelationshipRepository $petRelationshipRepository;

    public function __construct(PetRelationshipRepository $petRelationshipRepository)
    {
        $this->petRelationshipRepository = $petRelationshipRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:export-relationship-network')
            ->setDescription('Export relationship data in a format appropriate for https://www.d3-graph-gallery.com/graph/network_basic.html')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $relationships = $this->petRelationshipRepository->findAll();

        $pets = [];
        $links = [];

        foreach($relationships as $relationship)
        {
            $pet = $relationship->getPet();
            $pets[$pet->getId()] = [
                'id' => $pet->getId(),
                'owner' => $pet->getOwner()->getId(),
                'name' => $pet->getName() . ' #' . $pet->getId()
            ];

            $links[] = [
                'source' => $pet->getId(),
                'target' => $relationship->getRelationship()->getId(),
            ];
        }

        $pets = array_values($pets);

        echo \GuzzleHttp\json_encode([
            'links' => $links,
            'nodes' => $pets
        ]);

        return self::SUCCESS;
    }
}
