<?php

namespace App\Repository;

use App\Entity\GuildMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GuildMembership|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuildMembership|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuildMembership[]    findAll()
 * @method GuildMembership[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuildMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuildMembership::class);
    }

    // /**
    //  * @return GuildMembership[] Returns an array of GuildMembership objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GuildMembership
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
