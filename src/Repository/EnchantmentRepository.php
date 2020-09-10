<?php

namespace App\Repository;

use App\Entity\Enchantment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Enchantment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enchantment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enchantment[]    findAll()
 * @method Enchantment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnchantmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enchantment::class);
    }

    // /**
    //  * @return Enchantment[] Returns an array of Enchantment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Enchantment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
