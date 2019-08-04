<?php

namespace App\Repository;

use App\Entity\PetRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PetRelationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetRelationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetRelationship[]    findAll()
 * @method PetRelationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PetRelationship::class);
    }

    // /**
    //  * @return PetRelationship[] Returns an array of PetRelationship objects
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
    public function findOneBySomeField($value): ?PetRelationship
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
