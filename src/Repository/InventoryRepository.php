<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findOneByName(User $owner, string $itemName): ?Inventory
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->setParameter('user', $owner)
            ->leftJoin('i.item', 'item')
            ->andWhere('item.name=:itemName')
            ->setParameter('itemName', $itemName)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findFertilizers(User $user)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fertilizer>0')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function countItemsInHouse(User $user)
    {
        return (int)$this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:houseLocation')
            ->setParameter('user', $user)
            ->setParameter('houseLocation', LocationEnum::HOME)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
