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


namespace App\Service\Filter;

use App\Entity\PetRelationship;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class PetRelationshipFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    private readonly ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(PetRelationship::class, 'readonly');

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'commitment' => [ 'r.commitment' => 'desc' ],
            ],
            [
                'pet' => $this->filterPet(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('r')
            ->join('r.relationship', 'friend')
        ;
    }

    public function filterPet(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('r.pet = :pet')
            ->setParameter('pet', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
