<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserQuest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserQuest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserQuest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserQuest[]    findAll()
 * @method UserQuest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserQuestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserQuest::class);
    }

    public function findOrCreate(User $user, string $name, $default): UserQuest
    {
        $record = $this->findOneBy([
            'user' => $user,
            'name' => $name,
        ]);

        if(!$record)
        {
            $record = (new UserQuest())
                ->setUser($user)
                ->setName($name)
                ->setValue($default)
            ;

            $this->getEntityManager()->persist($record);
        }

        return $record;
    }

    // /**
    //  * @return UserQuest[] Returns an array of UserQuest objects
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
    public function findOneBySomeField($value): ?UserQuest
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
