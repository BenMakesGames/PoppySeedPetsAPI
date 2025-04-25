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

use App\Entity\PetGroup;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetGroupFilterService
{
    use FilterService;

    public const int PageSize = 20;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(PetGroup::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'lastmeton' => [ 'g.lastMetOn' => 'desc' ], // first one is the default
                'createdon' => [ 'g.createdOn' => 'desc' ],
                'name' => [ 'g.name' => 'asc' ],
            ],
            [
                'type' => $this->filterType(...),
                'withPetsOwnedBy' => $this->filterWithPetsOwnedBy(...),
                'name' => $this->filterName(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('g');
    }

    public function filterType(QueryBuilder $qb, $value): void
    {
        $qb
            ->andWhere('g.type = :type')
            ->setParameter('type', $value)
        ;
    }

    public function filterName(QueryBuilder $qb, $value): void
    {
        $qb
            ->andWhere('g.name LIKE :name')
            ->setParameter('name', '%' . StringFunctions::escapeMySqlWildcardCharacters($value) . '%')
        ;
    }

    public function filterWithPetsOwnedBy(QueryBuilder $qb, $value): void
    {
        if(is_numeric($value) && (int)$value == $value)
        {
            if(!in_array('p', $qb->getAllAliases()))
                $qb->leftJoin('g.members', 'p');
            
            $qb
                ->andWhere('p.owner=:owner')
                ->setParameter('owner', $value)
                ->groupBy('g')
            ;
        }
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
