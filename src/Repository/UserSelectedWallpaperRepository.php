<?php

namespace App\Repository;

use App\Entity\UserSelectedWallpaper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSelectedWallpaper>
 *
 * @method UserSelectedWallpaper|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSelectedWallpaper|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSelectedWallpaper[]    findAll()
 * @method UserSelectedWallpaper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSelectedWallpaperRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSelectedWallpaper::class);
    }

    public function add(UserSelectedWallpaper $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserSelectedWallpaper $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserSelectedWallpaper[] Returns an array of UserSelectedWallpaper objects
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

//    public function findOneBySomeField($value): ?UserSelectedWallpaper
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
