<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\InventoryRepository;
use Doctrine\ORM\QueryBuilder;

class MarketFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    /**
     * @var User
     */
    private $user;

    private $repository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->repository = $inventoryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'item.name' => 'asc', 'i.sellPrice' => 'asc' ], // first one is the default
                'price' => [ 'i.sellPrice' => 'asc', 'item.name' => 'asc' ],
            ],
            [
                'name' => array($this, 'filterName'),
                'edible' => array($this, 'filterEdible'),
                'equipable' => array($this, 'filterEquipable'),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->andWhere('i.sellPrice IS NOT NULL')
            ->andWhere('i.owner != :user')
            ->setParameter('user', $this->user->getId())
        ;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('item.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterEdible(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.food IS NULL');
        else
            $qb->andWhere('item.food IS NOT NULL');
    }

    public function filterEquipable(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.tool IS NULL');
        else
            $qb->andWhere('item.tool IS NOT NULL');
    }
}