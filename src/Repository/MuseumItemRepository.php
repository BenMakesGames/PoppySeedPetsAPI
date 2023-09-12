<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MuseumItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MuseumItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MuseumItem[]    findAll()
 * @method MuseumItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class MuseumItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MuseumItem::class);
    }

    public function hasUserDonated(User $user, Item $item): bool
    {
        return $this->count([ 'user' => $user, 'item' => $item ]) > 0;
    }
}
