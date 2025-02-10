<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetRepository extends ServiceEntityRepository
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
