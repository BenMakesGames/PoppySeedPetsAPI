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
use App\Service\PetGroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindPetsUnhappyInAGroupCommand extends Command
{
    private EntityManagerInterface $em;
    private PetGroupService $petGroupService;

    public function __construct(EntityManagerInterface $em, PetGroupService $petGroupService)
    {
        $this->em = $em;
        $this->petGroupService = $petGroupService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:find-pets-unhappy-in-a-group')
            ->setDescription('Find pets that are unhappy in at least one of their groups.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Pet[] $pets */
        $pets = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->join('p.groups', 'g')
            ->andWhere('g IS NOT NULL')
            ->getQuery()
            ->execute()
        ;

        $results = [];

        foreach($pets as $pet)
        {
            foreach($pet->getGroups() as $group)
            {
                $happiness = $this->petGroupService->getMemberHappiness($group, $pet);
                if($happiness < 0)
                {
                    $results[] = [
                        'pet' => $pet->getId(),
                        'owner' => $pet->getOwner()->getId(),
                        'group' => $group->getId(),
                        'happiness' => $happiness
                    ];
                }
            }
        }

        echo \GuzzleHttp\json_encode($results, JSON_PRETTY_PRINT);

        return 0;
    }
}
