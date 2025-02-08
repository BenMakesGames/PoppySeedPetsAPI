<?php
declare(strict_types=1);

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
            'name' => UserStyle::CURRENT
        ]);
    }

    public static function countThemesByUser(EntityManagerInterface $em, User $user): int
    {
        return (int)$em->getRepository(UserStyle::class)->createQueryBuilder('t') // mind the "(int)" cast!
            ->select('COUNT(t)')
            ->andWhere('t.user=:user')
            ->andWhere('t.name!=:current')
            ->setParameter('user', $user)
            ->setParameter('current', UserStyle::CURRENT)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
