<?php
namespace App\Service\Filter;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ArticleFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    private $repository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->repository = $articleRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'createdon' => [ 'a.createdOn' => 'desc' ], // first one is the default
            ],
            [
                'designGoal' => [ $this, 'filterDesignGoal' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('a');
    }

    public function filterDesignGoal(QueryBuilder $qb, $value)
    {
        if(!in_array('designGoals', $qb->getAllAliases()))
            $qb->join('a.designGoals', 'designGoals');

        $qb
            ->andWhere('designGoals.id=:designGoal')
            ->setParameter('designGoal', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb; // TODO: enable caching, but clear the cache when an article is created or updated
    }
}
