<?php

namespace App\Repository;

use App\Entity\RecipeAttempted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeAttempted|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeAttempted|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeAttempted[]    findAll()
 * @method RecipeAttempted[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class RecipeAttemptedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeAttempted::class);
    }
}
