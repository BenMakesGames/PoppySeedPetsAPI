<?php

namespace App\Repository;

use App\Entity\Plant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Plant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plant[]    findAll()
 * @method Plant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class PlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plant::class);
    }

    // /**
    //  * @return Plant[] Returns an array of Plant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Plant
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
