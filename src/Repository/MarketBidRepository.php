<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\MarketBid;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MarketBid|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketBid|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketBid[]    findAll()
 * @method MarketBid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class MarketBidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketBid::class);
    }

    public function getTotalQuantity(User $user)
    {
        return (int)$this->createQueryBuilder('m')
            ->select('SUM(m.quantity)')
            ->andWhere('m.user=:userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findHighestBidForItem(Inventory $inventory, int $minPrice): ?MarketBid
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.item=:item')
            ->andWhere('m.user!=:seller')
            ->andWhere('m.bid>=:minPrice')
            ->setParameter('item', $inventory->getItem())
            ->setParameter('seller', $inventory->getOwner())
            ->setParameter('minPrice', $minPrice)
            ->addOrderBy('m.bid', 'DESC')
            ->addOrderBy('m.createdOn', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
