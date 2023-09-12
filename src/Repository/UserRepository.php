<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\Squirrel3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public static function findOneRecentlyActive(EntityManagerInterface $em, User $except, int $hours = 24): ?User
    {
        $squirrel3 = new Squirrel3();
        $oneDayAgo = (new \DateTimeImmutable())->modify('-' . $hours . ' hours');

        $userRepository = $em->getRepository(User::class);

        $numberOfUsersQuery = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.lastActivity >= :oneDayAgo')
            ->setParameter('oneDayAgo', $oneDayAgo)
        ;

        if($except !== null)
        {
            $numberOfUsersQuery
                ->andWhere('u.id != :except')
                ->setParameter('except', $except->getId())
            ;
        }

        $numberOfUsers = (int)($numberOfUsersQuery->getQuery()->getSingleScalarResult());

        if($numberOfUsers === 0)
            return null;

        $offset = $squirrel3->rngNextInt(0, $numberOfUsers - 1);

        $userQuery = $userRepository->createQueryBuilder('u')
            ->andWhere('u.lastActivity >= :oneDayAgo')
            ->setParameter('oneDayAgo', $oneDayAgo)
        ;

        if($except !== null)
        {
            $userQuery
                ->andWhere('u.id != :except')
                ->setParameter('except', $except->getId())
            ;
        }

        $userQuery
            ->setFirstResult($offset)
            ->setMaxResults(1)
        ;

        return $userQuery->getQuery()->getSingleResult();
    }
}
