<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserStyle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserStyle|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStyle|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStyle[]    findAll()
 * @method UserStyle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStyleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserStyle::class);
    }

    public function findCurrent(User $user)
    {
        return $this->findOneBy([
            'user' => $user,
            'name' => UserStyle::CURRENT
        ]);
    }

    public function countThemesByUser(User $user): int
    {
        return (int)$this->createQueryBuilder('t') // mind the "(int)" cast!
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
