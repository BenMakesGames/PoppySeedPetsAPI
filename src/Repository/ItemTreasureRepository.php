<?php

namespace App\Repository;

use App\Entity\ItemTreasure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemTreasure|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemTreasure|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemTreasure[]    findAll()
 * @method ItemTreasure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemTreasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemTreasure::class);
    }

    // /**
    //  * @return ItemTreasure[] Returns an array of ItemTreasure objects
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
    public function findOneBySomeField($value): ?ItemTreasure
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
