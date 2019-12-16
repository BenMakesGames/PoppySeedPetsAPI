<?php

namespace App\Repository;

use App\Entity\PetGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PetGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetGroup[]    findAll()
 * @method PetGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PetGroup::class);
    }

    // /**
    //  * @return PetGroup[] Returns an array of PetGroup objects
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
    public function findOneBySomeField($value): ?PetGroup
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
