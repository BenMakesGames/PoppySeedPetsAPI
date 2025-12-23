<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Functions;

use App\Entity\HollowEarthTile;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Exceptions\PSPNotFoundException;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

final class HollowEarthTileRepository
{
    public static function findOneById(EntityManagerInterface $em, int $tileId): HollowEarthTile
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
    public static function findAllInBounds(EntityManagerInterface $em): array
    {
        return $em->getRepository(HollowEarthTile::class)->createQueryBuilder('t')
            ->andWhere('t.moveDirection != :zero')
            ->setParameter('zero', HollowEarthMoveDirectionEnum::Zero)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('HollowEarthTileRepository_FindAllInBounds'))
            ->execute()
        ;
    }

    public static function findRandom(EntityManagerInterface $em, IRandom $rng): ?HollowEarthTile
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
