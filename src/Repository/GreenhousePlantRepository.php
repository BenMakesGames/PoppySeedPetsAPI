<?php

namespace App\Repository;

use App\Entity\GreenhousePlant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GreenhousePlant|null find($id, $lockMode = null, $lockVersion = null)
 * @method GreenhousePlant|null findOneBy(array $criteria, array $orderBy = null)
 * @method GreenhousePlant[]    findAll()
 * @method GreenhousePlant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GreenhousePlantRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GreenhousePlant::class);
    }
}
