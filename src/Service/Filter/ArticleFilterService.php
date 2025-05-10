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

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ArticleFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 10;

    /** @var EntityRepository<Article> */
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(Article::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'createdon' => [ 'a.createdOn' => 'desc' ], // first one is the default
            ],
            [
                'designGoal' => $this->filterDesignGoal(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('a');
    }

    public function filterDesignGoal(QueryBuilder $qb, mixed $value): void
    {
        if(!in_array('designGoals', $qb->getAllAliases()))
            $qb->join('a.designGoals', 'designGoals');

        $qb
            ->andWhere('designGoals.id=:designGoal')
            ->setParameter('designGoal', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb; // TODO: enable caching, but clear the cache when an article is created or updated
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
