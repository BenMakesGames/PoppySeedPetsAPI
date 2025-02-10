<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use Doctrine\ORM\EntityManagerInterface;

final class PetRepository
{
    public static function getNumberAtHome(EntityManagerInterface $em, User $user): int
    {
        return (int)$em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.owner=:owner')
            ->andWhere('p.location=:home')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
