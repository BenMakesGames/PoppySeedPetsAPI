<?php

namespace App\Repository;

use App\Entity\HollowEarthTile;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Service\Squirrel3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HollowEarthTile|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthTile|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthTile[]    findAll()
 * @method HollowEarthTile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HollowEarthTileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HollowEarthTile::class);
    }

    public function findAllInBounds()
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.moveDirection != :zero')
            ->setParameter('zero', HollowEarthMoveDirectionEnum::ZERO)
            ->getQuery()
            ->execute()
        ;
    }

    public function findRandom(): HollowEarthTile
    {
        $squirrel3 = new Squirrel3();

        $numberOfTiles = (int)$this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $offset = $squirrel3->rngNextInt(0, $numberOfTiles - 1);

        return $this->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
