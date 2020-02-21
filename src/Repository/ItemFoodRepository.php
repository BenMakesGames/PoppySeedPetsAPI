<?php

namespace App\Repository;

use App\Entity\ItemFood;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemFood|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemFood|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemFood[]    findAll()
 * @method ItemFood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemFoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemFood::class);
    }

    // /**
    //  * @return ItemFood[] Returns an array of ItemFood objects
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
    public function findOneBySomeField($value): ?ItemFood
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
