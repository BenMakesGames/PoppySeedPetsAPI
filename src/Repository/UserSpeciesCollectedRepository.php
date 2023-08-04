<?php

namespace App\Repository;

use App\Entity\UserSpeciesCollected;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSpeciesCollected>
 *
 * @method UserSpeciesCollected|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSpeciesCollected|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSpeciesCollected[]    findAll()
 * @method UserSpeciesCollected[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSpeciesCollectedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSpeciesCollected::class);
    }

    public function add(UserSpeciesCollected $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserSpeciesCollected $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserSpeciesCollected[] Returns an array of UserSpeciesCollected objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserSpeciesCollected
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
