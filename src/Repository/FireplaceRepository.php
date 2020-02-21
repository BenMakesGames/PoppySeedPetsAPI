<?php

namespace App\Repository;

use App\Entity\Fireplace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fireplace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fireplace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fireplace[]    findAll()
 * @method Fireplace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FireplaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fireplace::class);
    }

    // /**
    //  * @return Fireplace[] Returns an array of Fireplace objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Fireplace
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
