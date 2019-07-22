<?php

namespace App\Repository;

use App\Entity\SpiritCompanion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SpiritCompanion|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpiritCompanion|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpiritCompanion[]    findAll()
 * @method SpiritCompanion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpiritCompanionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SpiritCompanion::class);
    }

    // /**
    //  * @return SpiritCompanion[] Returns an array of SpiritCompanion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SpiritCompanion
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
