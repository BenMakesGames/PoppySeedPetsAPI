<?php

namespace App\Repository;

use App\Entity\DailyStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailyStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyStats[]    findAll()
 * @method DailyStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyStats::class);
    }

    // /**
    //  * @return DailyStats[] Returns an array of DailyStats objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DailyStats
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
