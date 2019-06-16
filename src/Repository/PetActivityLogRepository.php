<?php

namespace App\Repository;

use App\Entity\PetActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PetActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLog[]    findAll()
 * @method PetActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PetActivityLog::class);
    }

    // /**
    //  * @return PetActivityLog[] Returns an array of PetActivityLog objects
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
    public function findOneBySomeField($value): ?PetActivityLog
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
