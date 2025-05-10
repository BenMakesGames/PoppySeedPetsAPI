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

use App\Entity\UserSpeciesCollected;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserSpeciesCollectedFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    /** @var EntityRepository<UserSpeciesCollected> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserSpeciesCollected::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'discoveredon' => [ 'd.discoveredOn' => 'desc' ], // first one is the default
                'speciesname' => [ 'species.nameSort' => 'asc' ],
                'id' => [ 'species.id' => 'asc' ],
            ],
            [
                'user' => $this->filterUser(...),
            ]
        );
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('d')
            ->innerJoin('d.species', 'species');
    }

    public function filterUser(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('d.user = :userId')
            ->setParameter('userId', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
