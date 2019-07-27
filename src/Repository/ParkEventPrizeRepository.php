<?php

namespace App\Repository;

use App\Entity\ParkEventPrize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParkEventPrize|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParkEventPrize|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParkEventPrize[]    findAll()
 * @method ParkEventPrize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParkEventPrizeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParkEventPrize::class);
    }

    // /**
    //  * @return ParkEventPrize[] Returns an array of ParkEventPrize objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParkEventPrize
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
