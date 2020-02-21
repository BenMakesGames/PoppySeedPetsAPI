<?php

namespace App\Repository;

use App\Entity\PetBaby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetBaby|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetBaby|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetBaby[]    findAll()
 * @method PetBaby[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetBabyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetBaby::class);
    }

    // /**
    //  * @return PetBaby[] Returns an array of PetBaby objects
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
    public function findOneBySomeField($value): ?PetBaby
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
