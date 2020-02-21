<?php

namespace App\Repository;

use App\Entity\Greenhouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Greenhouse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Greenhouse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Greenhouse[]    findAll()
 * @method Greenhouse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GreenhouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Greenhouse::class);
    }

    // /**
    //  * @return Greenhouse[] Returns an array of Greenhouse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Greenhouse
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
