<?php

namespace App\Repository;

use App\Entity\GreenhousePlant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GreenhousePlant|null find($id, $lockMode = null, $lockVersion = null)
 * @method GreenhousePlant|null findOneBy(array $criteria, array $orderBy = null)
 * @method GreenhousePlant[]    findAll()
 * @method GreenhousePlant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class GreenhousePlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GreenhousePlant::class);
    }
}
