<?php

namespace App\Repository;

use App\Entity\PetSpecies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetSpecies|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetSpecies|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetSpecies[]    findAll()
 * @method PetSpecies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetSpeciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetSpecies::class);
    }

    // /**
    //  * @return PetSpecies[] Returns an array of PetSpecies objects
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
    public function findOneBySomeField($value): ?PetSpecies
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
