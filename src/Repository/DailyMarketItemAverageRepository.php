<?php

namespace App\Repository;

use App\Entity\DailyMarketItemAverage;
use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailyMarketItemAverage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyMarketItemAverage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyMarketItemAverage[]    findAll()
 * @method DailyMarketItemAverage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class DailyMarketItemAverageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyMarketItemAverage::class);
    }

    /**
     * @return DailyMarketItemAverage[]|Collection
     */
    public function findHistoryForItem(Item $item, \DateInterval $maxAge): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.item=:item')
            ->andWhere('h.date>=:earliestDate')
            ->setParameter('item', $item)
            ->setParameter('earliestDate', (new \DateTimeImmutable())->sub($maxAge)->format('Y-m-d'))
            ->getQuery()
            ->execute()
        ;
    }

    public function findLastHistoryForItem(Item $item): ?DailyMarketItemAverage
    {
        return $this->findOneBy([ 'item' => $item ], [ 'date' => 'DESC' ]);
    }
}
