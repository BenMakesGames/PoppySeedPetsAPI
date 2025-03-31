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

class AssignAffectionExpressionsCommand extends PoppySeedPetsCommand
{
    private EntityManagerInterface $em;
    private IRandom $rng;

    public function __construct(
        EntityManagerInterface $em, IRandom $rng
    )
    {
        $this->em = $em;
        $this->rng = $rng;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:assign-affection-expressions')
            ->setDescription('Assign expressions to all pets that don\'t have them.')
        ;
    }

    protected function doCommand(): int
    {
        do
        {
            $pets = $this->em->getRepository(Pet::class)->findBy([ 'affectionExpressions' => '' ], null, 200);

            foreach ($pets as $pet) {
                $pet->assignAffectionExpressions($this->rng);
            }

            $this->em->flush();
            $this->em->clear();
        } while(count($pets) > 0);

        return 0;
    }
}