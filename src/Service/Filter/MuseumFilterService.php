<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\MuseumItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MuseumFilterService
{
    use FilterService;

    public const PageSize = 20;

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

    public function filterUser(QueryBuilder $qb, $value)
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
}
