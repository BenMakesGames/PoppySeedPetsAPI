<?php
namespace App\Service\Filter;

use App\Repository\InventoryRepository;
use Doctrine\ORM\QueryBuilder;

class InventoryFilterService
{
    use FilterService;

    public const PAGE_SIZE = 100;

    private $repository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->repository = $inventoryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'itemName' => [ 'item.name' => 'asc' ], // first one is the default
            ],
            [
                'location' => [ $this, 'filterLocation' ],
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.owner = :userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterLocation(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.location = :location')
            ->setParameter('location', $value)
        ;
    }
}