<?php

namespace App\Repository;

use App\Entity\Aura;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Aura|null find($id, $lockMode = null, $lockVersion = null)
 * @method Aura|null findOneBy(array $criteria, array $orderBy = null)
 * @method Aura[]    findAll()
 * @method Aura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class AuraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aura::class);
    }
}
