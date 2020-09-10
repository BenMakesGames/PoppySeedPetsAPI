<?php

namespace App\Repository;

use App\Entity\PetHouseTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetHouseTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetHouseTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetHouseTime[]    findAll()
 * @method PetHouseTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetHouseTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetHouseTime::class);
    }

    // /**
    //  * @return PetHouseTime[] Returns an array of PetHouseTime objects
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
    public function findOneBySomeField($value): ?PetHouseTime
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
