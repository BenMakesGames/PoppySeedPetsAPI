<?php

namespace App\Repository;

use App\Entity\MonsterOfTheWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonsterOfTheWeek>
 *
 * @method MonsterOfTheWeek|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonsterOfTheWeek|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonsterOfTheWeek[]    findAll()
 * @method MonsterOfTheWeek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonsterOfTheWeekRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonsterOfTheWeek::class);
    }

//    /**
//     * @return MonsterOfTheWeek[] Returns an array of MonsterOfTheWeek objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MonsterOfTheWeek
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
