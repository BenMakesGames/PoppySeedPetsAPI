<?php

namespace App\Repository;

use App\Entity\ParkEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParkEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParkEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParkEvent[]    findAll()
 * @method ParkEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class ParkEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParkEvent::class);
    }

    // /**
    //  * @return ParkEvent[] Returns an array of ParkEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParkEvent
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
