<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MonthlyStoryAdventure|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyStoryAdventure|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyStoryAdventure[]    findAll()
 * @method MonthlyStoryAdventure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlyStoryAdventureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyStoryAdventure::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(MonthlyStoryAdventure $entity, bool $flush = true): void
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
    public function remove(MonthlyStoryAdventure $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return MonthlyStoryAdventure[] Returns an array of MonthlyStoryAdventure objects
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
    public function findOneBySomeField($value): ?MonthlyStoryAdventure
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
