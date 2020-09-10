<?php

namespace App\Repository;

use App\Entity\PlantYield;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlantYield|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlantYield|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlantYield[]    findAll()
 * @method PlantYield[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlantYieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlantYield::class);
    }

    // /**
    //  * @return PlantYield[] Returns an array of PlantYield objects
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
    public function findOneBySomeField($value): ?PlantYield
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
