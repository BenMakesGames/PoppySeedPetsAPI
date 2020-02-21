<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $emailAddress): ?User
    {
        return $this->findOneBy([ 'email' => $emailAddress ]);
    }

    public function findOneRecentlyActive(?User $except): ?User
    {
        $oneDayAgo = (new \DateTimeImmutable())->modify('-24 hours');

        $numberOfUsersQuery = $this->createQueryBuilder('u')
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

        $offset = mt_rand(0, $numberOfUsers - 1);

        $userQuery = $this->createQueryBuilder('u')
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
