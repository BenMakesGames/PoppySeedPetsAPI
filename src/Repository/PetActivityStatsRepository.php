<?php

namespace App\Repository;

use App\Entity\PetActivityStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PetActivityStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityStats[]    findAll()
 * @method PetActivityStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetActivityStatsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PetActivityStats::class);
    }

    // /**
    //  * @return PetActivityStats[] Returns an array of PetActivityStats objects
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
    public function findOneBySomeField($value): ?PetActivityStats
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
