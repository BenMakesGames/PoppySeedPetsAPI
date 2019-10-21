<?php

namespace App\Repository;

use App\Entity\ItemHat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemHat|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemHat|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemHat[]    findAll()
 * @method ItemHat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemHatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemHat::class);
    }

    // /**
    //  * @return ItemHat[] Returns an array of ItemHat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemHat
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
