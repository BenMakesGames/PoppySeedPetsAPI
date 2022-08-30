<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventure;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMonthlyStoryAdventureStepCompleted|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMonthlyStoryAdventureStepCompleted|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMonthlyStoryAdventureStepCompleted[]    findAll()
 * @method UserMonthlyStoryAdventureStepCompleted[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMonthlyStoryAdventureStepCompletedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMonthlyStoryAdventureStepCompleted::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(UserMonthlyStoryAdventureStepCompleted $entity, bool $flush = true): void
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
    public function remove(UserMonthlyStoryAdventureStepCompleted $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return UserMonthlyStoryAdventureStepCompleted[] Returns an array of UserMonthlyStoryAdventureStepCompleted objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserMonthlyStoryAdventureStepCompleted
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findComplete(User $user, MonthlyStoryAdventure $adventure)
    {
        return $this->createQueryBuilder('c')
            ->join('c.adventureStep', 's')
            ->andWhere('c.user = :user')
            ->andWhere('s.adventure = :adventure')
            ->setParameter('user', $user)
            ->setParameter('adventure', $adventure)
            ->getQuery()
            ->execute();
    }
}
