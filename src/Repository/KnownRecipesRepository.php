<?php

namespace App\Repository;

use App\Entity\KnownRecipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method KnownRecipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method KnownRecipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method KnownRecipes[]    findAll()
 * @method KnownRecipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KnownRecipesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, KnownRecipes::class);
    }

    // /**
    //  * @return KnownRecipes[] Returns an array of KnownRecipes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?KnownRecipes
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
