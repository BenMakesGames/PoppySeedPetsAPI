<?php
namespace App\Service\Filter;

use App\Entity\KnownRecipes;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class KnownRecipesFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(KnownRecipes::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'r.name' => 'ASC' ], // first one is the default
            ],
            [
                'name' => [ $this, 'filterName' ],
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('kr')
            ->leftJoin('kr.recipe', 'r')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('kr.user=:userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('r.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
