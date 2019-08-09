<?php

namespace App\Repository;

use App\Entity\ItemPlant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemPlant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemPlant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemPlant[]    findAll()
 * @method ItemPlant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemPlantRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemPlant::class);
    }

    // /**
    //  * @return ItemPlant[] Returns an array of ItemPlant objects
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
    public function findOneBySomeField($value): ?ItemPlant
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
