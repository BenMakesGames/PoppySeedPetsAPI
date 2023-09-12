<?php

namespace App\Repository;

use App\Entity\HollowEarthPlayerTile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HollowEarthPlayerTile|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthPlayerTile|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthPlayerTile[]    findAll()
 * @method HollowEarthPlayerTile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class HollowEarthPlayerTileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HollowEarthPlayerTile::class);
    }

    // /**
    //  * @return HollowEarthPlayerTile[] Returns an array of HollowEarthPlayerTile objects
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
    public function findOneBySomeField($value): ?HollowEarthPlayerTile
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
