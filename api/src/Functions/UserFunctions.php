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
use Doctrine\ORM\EntityManagerInterface;

final class UserFunctions
{
    public static function findOneRecentlyActive(EntityManagerInterface $em, User $except, int $hours = 24): ?User
    {
        $oneDayAgo = (new \DateTimeImmutable())->modify('-' . $hours . ' hours');

        $userRepository = $em->getRepository(User::class);

        $numberOfUsersQuery = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.lastActivity >= :oneDayAgo')
            ->andWhere('u.id != :except')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('except', $except->getId())
        ;

        $numberOfUsers = (int)($numberOfUsersQuery->getQuery()->getSingleScalarResult());

        if($numberOfUsers === 0)
            return null;

        $offset = random_int(0, $numberOfUsers - 1);

        $userQuery = $userRepository->createQueryBuilder('u')
            ->andWhere('u.lastActivity >= :oneDayAgo')
            ->andWhere('u.id != :except')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('except', $except->getId())
        ;

        $userQuery
            ->setFirstResult($offset)
            ->setMaxResults(1)
        ;

        return $userQuery->getQuery()->getSingleResult();
    }
}
