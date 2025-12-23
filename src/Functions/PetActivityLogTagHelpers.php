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

use App\Entity\PetActivityLogTag;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final class PetActivityLogTagHelpers
{
    /**
     * @param string[] $names
     * @return PetActivityLogTag[]
     */
    public static function findByNames(EntityManagerInterface $em, array $names): array
    {
        $tags = [];

        foreach($names as $name)
            $tags[] = PetActivityLogTagHelpers::findOneByName($em, $name);

        return $tags;
    }

    public static function findOneByName(EntityManagerInterface $em, string $name): PetActivityLogTag
    {
        $tag = $em->getRepository(PetActivityLogTag::class)->createQueryBuilder('t')
            ->where('t.title=:title')
            ->setParameter('title', $name)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('PetActivityLogTagRepository_FindOneByName_' . $name))
            ->getOneOrNullResult();

        if(!$tag)
            throw new \InvalidArgumentException('Could not find tag with name "' . $name . '"!');

        return $tag;
    }
}
