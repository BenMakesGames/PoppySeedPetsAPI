<?php

namespace App\Repository;

use App\Entity\PetActivityLogItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PetActivityLogItem>
 *
 * @method PetActivityLogItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLogItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLogItem[]    findAll()
 * @method PetActivityLogItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetActivityLogItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetActivityLogItem::class);
    }

//    /**
//     * @return PetActivityLogItem[] Returns an array of PetActivityLogItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PetActivityLogItem
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
