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

use App\Entity\Spice;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class SpiceRepository
{
    public static function findOneByName(EntityManagerInterface $em, string $spiceName): ?Spice
    {
        $spice = $em->getRepository(Spice::class)->createQueryBuilder('s')
            ->where('s.name=:name')
            ->setParameter('name', $spiceName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('SpiceRepository_FindOneByName_' . $spiceName))
            ->getOneOrNullResult();

        if(!$spice) throw new PSPNotFoundException('There is no spice called ' . $spiceName . '.');

        return $spice;
    }
}
