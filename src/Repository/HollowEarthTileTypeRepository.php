<?php

namespace App\Repository;

use App\Entity\HollowEarthTileType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HollowEarthTileType|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthTileType|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthTileType[]    findAll()
 * @method HollowEarthTileType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HollowEarthTileTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HollowEarthTileType::class);
    }

    // /**
    //  * @return HollowEarthTileType[] Returns an array of HollowEarthTileType objects
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
    public function findOneBySomeField($value): ?HollowEarthTileType
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
