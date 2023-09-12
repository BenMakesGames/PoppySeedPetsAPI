<?php

namespace App\Repository;

use App\Entity\UserMenuOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMenuOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMenuOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMenuOrder[]    findAll()
 * @method UserMenuOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserMenuOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMenuOrder::class);
    }

    // /**
    //  * @return UserMenuOrder[] Returns an array of UserMenuOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserMenuOrder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
