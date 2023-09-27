<?php

namespace App\Repository;

use App\Entity\HollowEarthTile;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Service\IRandom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HollowEarthTile|null find($id, $lockMode = null, $lockVersion = null)
 * @method HollowEarthTile|null findOneBy(array $criteria, array $orderBy = null)
 * @method HollowEarthTile[]    findAll()
 * @method HollowEarthTile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class HollowEarthTileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HollowEarthTile::class);
    }

    /**
     * @return HollowEarthTile[]
     */
    public static function findAllInBounds(EntityManagerInterface $em)
    {
        return $em->getRepository(HollowEarthTile::class)->createQueryBuilder('t')
            ->andWhere('t.moveDirection != :zero')
            ->setParameter('zero', HollowEarthMoveDirectionEnum::ZERO)
            ->getQuery()
            ->execute()
        ;
    }

    public static function findRandom(EntityManagerInterface $em, IRandom $rng): HollowEarthTile
    {
        $numberOfTiles = (int)$em->getRepository(HollowEarthTile::class)->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $offset = $rng->rngNextInt(0, $numberOfTiles - 1);

        return $em->getRepository(HollowEarthTile::class)->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
