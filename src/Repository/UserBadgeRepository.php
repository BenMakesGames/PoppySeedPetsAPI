<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBadge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBadge>
 *
 * @method UserBadge|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBadge|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBadge[]    findAll()
 * @method UserBadge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBadge::class);
    }

    public function add(UserBadge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserBadge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getClaimedBadgeNames(User $owner)
    {
        return $this->createQueryBuilder('b')
            ->select('b.badge')
            ->andWhere('b.user = :user')
            ->setParameter('user', $owner)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
