<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ArticleFilterService
{
    use FilterService;

    public const PageSize = 10;

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

    public function filterDesignGoal(QueryBuilder $qb, $value)
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
}
