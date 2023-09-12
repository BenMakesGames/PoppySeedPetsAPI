<?php

namespace App\Repository;

use App\Entity\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SurveyQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyQuestion[]    findAll()
 * @method SurveyQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class SurveyQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyQuestion::class);
    }

    // /**
    //  * @return SurveyQuestion[] Returns an array of SurveyQuestion objects
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
    public function findOneBySomeField($value): ?SurveyQuestion
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
