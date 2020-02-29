<?php

namespace App\Repository;

use App\Entity\TotemPole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TotemPole|null find($id, $lockMode = null, $lockVersion = null)
 * @method TotemPole|null findOneBy(array $criteria, array $orderBy = null)
 * @method TotemPole[]    findAll()
 * @method TotemPole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TotemPoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TotemPole::class);
    }

    // /**
    //  * @return TotemPole[] Returns an array of TotemPole objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TotemPole
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
