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
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignActivityPersonalitiesCommand extends Command
{
    public const BATCH_SIZE = 200;

    private EntityManagerInterface $em;
    private IRandom $rng;

    public function __construct(EntityManagerInterface $em, IRandom $squirrel3)
    {
        $this->em = $em;
        $this->rng = $squirrel3;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:assign-activity-personalities')
            ->setDescription('Assign activity personalities to pets that don\'t have one.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $pets = $this->getPetsWithoutActivityPersonalities();

        if(count($pets) === 0)
        {
            echo 'No pets to update.' . "\n";
            return 0;
        }

        while(count($pets) > 0)
        {
            foreach($pets as $pet)
                $pet->assignActivityPersonality($this->rng);

            $this->em->flush();
            $this->em->clear();

            echo 'Updated ' . count($pets) . ' pets.' . "\n";

            $pets = $this->getPetsWithoutActivityPersonalities();
        }

        return 0;
    }

    /**
     * @return Pet[]
     */
    private function getPetsWithoutActivityPersonalities(): array
    {
        return $this->em->getRepository(Pet::class)->findBy([ 'activityPersonality' => 0 ], null, self::BATCH_SIZE);
    }

}
