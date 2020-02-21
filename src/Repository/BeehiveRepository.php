<?php

namespace App\Repository;

use App\Entity\Beehive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Beehive|null find($id, $lockMode = null, $lockVersion = null)
 * @method Beehive|null findOneBy(array $criteria, array $orderBy = null)
 * @method Beehive[]    findAll()
 * @method Beehive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeehiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Beehive::class);
    }

    // /**
    //  * @return Beehive[] Returns an array of Beehive objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Beehive
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
