<?php

namespace App\Repository;

use App\Entity\InventoryForSale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryForSale>
 *
 * @method InventoryForSale|null find($id, $lockMode = null, $lockVersion = null)
 * @method InventoryForSale|null findOneBy(array $criteria, array $orderBy = null)
 * @method InventoryForSale[]    findAll()
 * @method InventoryForSale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryForSaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryForSale::class);
    }

//    /**
//     * @return InventoryForSale[] Returns an array of InventoryForSale objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InventoryForSale
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
