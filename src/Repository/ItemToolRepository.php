<?php

namespace App\Repository;

use App\Entity\ItemTool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemTool|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemTool|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemTool[]    findAll()
 * @method ItemTool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemToolRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemTool::class);
    }

    // /**
    //  * @return ItemTool[] Returns an array of ItemTool objects
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
    public function findOneBySomeField($value): ?ItemTool
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
