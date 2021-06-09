<?php

namespace App\Repository;

use App\Entity\Aura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Aura|null find($id, $lockMode = null, $lockVersion = null)
 * @method Aura|null findOneBy(array $criteria, array $orderBy = null)
 * @method Aura[]    findAll()
 * @method Aura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aura::class);
    }

    // /**
    //  * @return Aura[] Returns an array of Aura objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Aura
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
