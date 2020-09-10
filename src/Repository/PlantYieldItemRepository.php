<?php

namespace App\Repository;

use App\Entity\PlantYieldItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlantYieldItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlantYieldItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlantYieldItem[]    findAll()
 * @method PlantYieldItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlantYieldItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlantYieldItem::class);
    }

    // /**
    //  * @return PlantYieldItem[] Returns an array of PlantYieldItem objects
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
    public function findOneBySomeField($value): ?PlantYieldItem
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
