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

use App\Entity\UserActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserActivityLogsFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    /** @var EntityRepository<UserActivityLog> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserActivityLog::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'id' => [ 'l.id' => 'desc' ], // first one is the default
            ],
            [
                'tags' => $this->filterTags(...),
                'user' => $this->filterUser(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l');
    }

    public function filterUser(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('l.user=:userId')
            ->setParameter('userId', (int)$value)
        ;
    }

    public function filterTags(QueryBuilder $qb, mixed $value): void
    {
        if(!in_array('tags', $qb->getAllAliases()))
            $qb->leftJoin('l.tags', 'tags');

        if(is_array($value))
            $qb->andWhere('tags.title IN (:tags)');
        else
            $qb->andWhere('tags.title=:tags');

        $qb->setParameter('tags', $value);
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
