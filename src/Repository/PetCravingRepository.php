<?php

namespace App\Repository;

use App\Entity\PetCraving;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetCraving|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetCraving|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetCraving[]    findAll()
 * @method PetCraving[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetCravingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetCraving::class);
    }

    // /**
    //  * @return PetCraving[] Returns an array of PetCraving objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PetCraving
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
