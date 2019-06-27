<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStats[]    findAll()
 * @method UserStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserStats::class);
    }

    public function incrementStat(User $user, string $name, int $change = 1): UserStats
    {
        $stat = $this->findOneBy([
            'user' => $user,
            'stat' => $name
        ]);

        if(!$stat)
        {
            $stat = (new UserStats())
                ->setUser($user)
                ->setStat($name)
                ->increaseValue($change)
            ;

            $this->getEntityManager()->persist($stat);
        }
        else
        {
            $stat->increaseValue($change);
        }

        return $stat;
    }

    // /**
    //  * @return UserStats[] Returns an array of UserStats objects
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
    public function findOneBySomeField($value): ?UserStats
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
