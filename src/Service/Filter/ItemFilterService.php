<?php
namespace App\Service\Filter;

use App\Repository\ItemRepository;
use Doctrine\ORM\QueryBuilder;

class ItemFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->filterer = new Filterer(
            $itemRepository, 'i',
            self::PAGE_SIZE,
            [
                'name' => [ 'i.name', 'asc' ], // first one is the default
                'id' => [ 'i.id', 'asc' ],
            ],
            [
                'name' => array($this, 'filterName'),
                'edible' => array($this, 'filterEdible')
            ]
        );
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterEdible(QueryBuilder $qb, $value)
    {
        if($value)
            $qb->andWhere('i.food != :foodNull');
        else
            $qb->andWhere('i.food = :foodNull');

        $qb->setParameter('foodNull', 'N;');
    }
}