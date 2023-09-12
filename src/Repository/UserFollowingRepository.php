<?php

namespace App\Repository;

use App\Entity\UserFollowing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserFollowing|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollowing|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollowing[]    findAll()
 * @method UserFollowing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserFollowingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFollowing::class);
    }
}
