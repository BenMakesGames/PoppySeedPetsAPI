<?php

namespace App\Repository;

use App\Entity\MonsterOfTheWeekContribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonsterOfTheWeekContribution>
 *
 * @method MonsterOfTheWeekContribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonsterOfTheWeekContribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonsterOfTheWeekContribution[]    findAll()
 * @method MonsterOfTheWeekContribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonsterOfTheWeekContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonsterOfTheWeekContribution::class);
    }

//    /**
//     * @return MonsterOfTheWeekContribution[] Returns an array of MonsterOfTheWeekContribution objects
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

//    public function findOneBySomeField($value): ?MonsterOfTheWeekContribution
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
