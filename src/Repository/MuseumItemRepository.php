<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MuseumItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MuseumItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MuseumItem[]    findAll()
 * @method MuseumItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MuseumItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MuseumItem::class);
    }

    public function hasUserDonated(User $user, Item $item)
    {
        return $this->findOneBy([ 'user' => $user, 'item' => $item ]) !== null;
    }

    // /**
    //  * @return MuseumItem[] Returns an array of MuseumItem objects
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
    public function findOneBySomeField($value): ?MuseumItem
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
