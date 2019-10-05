<?php

namespace App\Repository;

use App\Entity\HollowEarthZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HollowEarthZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthZone[]    findAll()
 * @method HollowEarthZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HollowEarthZoneRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HollowEarthZone::class);
    }

    // /**
    //  * @return HollowEarthZone[] Returns an array of HollowEarthZone objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HollowEarthZone
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
