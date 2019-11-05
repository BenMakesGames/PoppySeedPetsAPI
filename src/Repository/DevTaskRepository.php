<?php

namespace App\Repository;

use App\Entity\DevTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DevTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method DevTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method DevTask[]    findAll()
 * @method DevTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DevTaskRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DevTask::class);
    }

    // /**
    //  * @return DevTask[] Returns an array of DevTask objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DevTask
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
