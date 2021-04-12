<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserStyle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserStyle|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStyle|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStyle[]    findAll()
 * @method UserStyle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStyleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserStyle::class);
    }

    public function findCurrent(User $user)
    {
        return $this->findOneBy([
            'user' => $user,
            'name' => UserStyle::CURRENT
        ]);
    }

    // /**
    //  * @return UserStyle[] Returns an array of UserStyle objects
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
    public function findOneBySomeField($value): ?UserStyle
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
