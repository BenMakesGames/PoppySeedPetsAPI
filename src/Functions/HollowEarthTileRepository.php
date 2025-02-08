<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\HollowEarthTile;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

final class HollowEarthTileRepository
{
    public static function findOneById(EntityManagerInterface $em, int $tileId)
    {
        $tile = $em->getRepository(HollowEarthTile::class)->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->setParameter('id', $tileId)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('HollowEarthTileRepository_FindOneById_' . $tileId))
            ->getOneOrNullResult()
        ;

        if(!$tile) throw new PSPNotFoundException('There is no tile #' . $tileId . '.');

        return $tile;
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
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('HollowEarthTileRepository_FindAllInBounds'))
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
