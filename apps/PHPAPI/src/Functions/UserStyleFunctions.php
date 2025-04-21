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

use App\Entity\User;
use App\Entity\UserStyle;
use Doctrine\ORM\EntityManagerInterface;

final class UserStyleFunctions
{
    public static function findCurrent(EntityManagerInterface $em, int $userId)
    {
        return $em->getRepository(UserStyle::class)->findOneBy([
            'user' => $userId,
            'name' => UserStyle::Current
        ]);
    }

    public static function countThemesByUser(EntityManagerInterface $em, User $user): int
    {
        return (int)$em->getRepository(UserStyle::class)->createQueryBuilder('t') // mind the "(int)" cast!
            ->select('COUNT(t)')
            ->andWhere('t.user=:user')
            ->andWhere('t.name!=:current')
            ->setParameter('user', $user)
            ->setParameter('current', UserStyle::Current)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
