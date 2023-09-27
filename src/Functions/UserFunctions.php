<?php

namespace App\Functions;

use App\Entity\User;
use App\Service\Squirrel3;
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
