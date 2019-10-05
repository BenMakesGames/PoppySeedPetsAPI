<?php

namespace App\Repository;

use App\Entity\HollowEarthTile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HollowEarthTile|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthTile|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthTile[]    findAll()
 * @method HollowEarthTile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HollowEarthTileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HollowEarthTile::class);
    }

    // /**
    //  * @return HollowEarthTile[] Returns an array of HollowEarthTile objects
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
    public function findOneBySomeField($value): ?HollowEarthTile
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
