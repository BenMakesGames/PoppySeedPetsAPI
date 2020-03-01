<?php

namespace App\Repository;

use App\Entity\TotemPoleTotem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TotemPoleTotem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TotemPoleTotem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TotemPoleTotem[]    findAll()
 * @method TotemPoleTotem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TotemPoleTotemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TotemPoleTotem::class);
    }

    // /**
    //  * @return TotemPoleTotem[] Returns an array of TotemPoleTotem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TotemPoleTotem
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
