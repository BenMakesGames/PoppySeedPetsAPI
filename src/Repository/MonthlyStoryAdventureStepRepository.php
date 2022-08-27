<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventureStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MonthlyStoryAdventureStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyStoryAdventureStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyStoryAdventureStep[]    findAll()
 * @method MonthlyStoryAdventureStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlyStoryAdventureStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyStoryAdventureStep::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(MonthlyStoryAdventureStep $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(MonthlyStoryAdventureStep $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return MonthlyStoryAdventureStep[] Returns an array of MonthlyStoryAdventureStep objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MonthlyStoryAdventureStep
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
