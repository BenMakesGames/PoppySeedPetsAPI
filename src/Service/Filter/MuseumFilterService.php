<?php
namespace App\Service\Filter;

use App\Repository\MuseumItemRepository;
use Doctrine\ORM\QueryBuilder;

class MuseumFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(MuseumItemRepository $museumItemRepository)
    {
        $this->repository = $museumItemRepository;

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
}
