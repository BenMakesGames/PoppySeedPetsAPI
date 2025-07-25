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

use App\Entity\MuseumItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MuseumFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    /** @var EntityRepository<MuseumItem> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(MuseumItem::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'donatedon' => [ 'm.donatedOn' => 'desc', 'item.name' => 'asc' ], // first one is the default
                'itemname' => [ 'item.name' => 'asc' ],
            ],
            [
                'user' => $this->filterUser(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('m')
            ->leftJoin('m.item', 'item')
        ;
    }

    public function filterUser(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('m.user = :userId')
            ->setParameter('userId', (int)$value)
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
