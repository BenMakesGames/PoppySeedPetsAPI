<?php

namespace App\Repository;

use App\Entity\UserUnlockedAura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserUnlockedAura|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUnlockedAura|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUnlockedAura[]    findAll()
 * @method UserUnlockedAura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUnlockedAuraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserUnlockedAura::class);
    }
}
