<?php

namespace App\Repository;

use App\Entity\Gatherable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Gatherable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gatherable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gatherable[]    findAll()
 * @method Gatherable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GatherableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Gatherable::class);
    }

}
