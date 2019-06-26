<?php

namespace App\Repository;

use App\Entity\UserFriend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserFriend|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFriend|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFriend[]    findAll()
 * @method UserFriend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFriendRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserFriend::class);
    }

    // /**
    //  * @return UserFriend[] Returns an array of UserFriend objects
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
    public function findOneBySomeField($value): ?UserFriend
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
