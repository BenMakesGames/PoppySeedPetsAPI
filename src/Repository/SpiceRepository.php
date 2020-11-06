<?php

namespace App\Repository;

use App\Entity\Spice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Spice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spice[]    findAll()
 * @method Spice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spice::class);
    }

    // /**
    //  * @return Spice[] Returns an array of Spice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Spice
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
