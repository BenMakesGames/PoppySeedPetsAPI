<?php
namespace App\Service\Filter;

use App\Repository\ArticleRepository;
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
                'designgoal' => [ $this, 'filterDesignGoal' ],
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
            $qb->join('p.designGoals', 'designGoals');

        $qb
            ->andWhere('designGoals.name=:designGoal')
            ->setParameter('designGoal', $value)
        ;
    }
}
