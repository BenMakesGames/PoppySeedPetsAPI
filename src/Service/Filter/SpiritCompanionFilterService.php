<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\SpiritCompanion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class SpiritCompanionFilterService
{
    use FilterService;

    public const PageSize = 20;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(SpiritCompanion::class);

        $this->filterer = new Filterer(
            self::PageSize,
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
