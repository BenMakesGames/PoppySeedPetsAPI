<?php
namespace App\Service\Filter;

use App\Entity\MuseumItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MuseumFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(MuseumItem::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'donatedon' => [ 'm.donatedOn' => 'desc' ], // first one is the default
                'itemname' => [ 'item.name' => 'asc' ],
            ],
            [
                'user' => [ $this, 'filterUser' ],
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
            ->setParameter('userId', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
