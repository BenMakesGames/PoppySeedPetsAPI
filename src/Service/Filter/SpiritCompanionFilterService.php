<?php
namespace App\Service\Filter;

use App\Repository\SpiritCompanionRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class SpiritCompanionFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(SpiritCompanionRepository $spiritCompanionRepository)
    {
        $this->repository = $spiritCompanionRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'p.id' => 'asc' ],
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
