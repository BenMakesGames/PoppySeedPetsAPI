<?php

namespace App\Repository;

use App\Entity\TradesUnlocked;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TradesUnlocked|null find($id, $lockMode = null, $lockVersion = null)
 * @method TradesUnlocked|null findOneBy(array $criteria, array $orderBy = null)
 * @method TradesUnlocked[]    findAll()
 * @method TradesUnlocked[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class TradesUnlockedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradesUnlocked::class);
    }

    // /**
    //  * @return TradesUnlocked[] Returns an array of TradesUnlocked objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TradesUnlocked
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
