<?php
namespace App\Service\Filter;

use App\Entity\Merit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MeritFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(Merit::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'm.name' => 'asc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('m');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
