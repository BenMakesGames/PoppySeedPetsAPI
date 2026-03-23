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

use App\Entity\Pet;
use App\Service\PetSocialActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetRelationshipsToHangOutWithCommand extends Command
{
    public function __construct(
        private readonly PetSocialActivityService $petSocialActivityService,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct();
    }

    protected function configure(): void
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

        $pet = $this->em->getRepository(Pet::class)->find($petId);

        if(!$pet)
            throw new \InvalidArgumentException('pet #' . $petId . ' does not exist.');

        $output->writeln($pet->getName() . ' (#' . $petId . ')');

        $time = microtime(true);
        $relationships = $this->petSocialActivityService->getRelationshipsToHangOutWith($pet);
        $time = microtime(true) - $time;

        $output->writeln('Found ' . count($relationships) . ' in ' . round($time, 2) . 's:');

        foreach($relationships as $r)
        {
            $friend = $r->getRelationship();

            $output->writeln('* ' . $friend->getName() . ' (#' . $friend->getId() . ')');
        }

        return self::SUCCESS;
    }
}
