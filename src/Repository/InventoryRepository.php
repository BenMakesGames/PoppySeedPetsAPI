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

    public function findOneToConsume(User $owner, string $itemName): ?Inventory
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.name=:itemName')
            ->setParameter('user', $owner)
            ->setParameter('consumableLocations', Inventory::CONSUMABLE_LOCATIONS)
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
            ->andWhere('i.location = :home')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fertilizer>0')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFuel(User $user)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:home)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fuel>0')
            ->addOrderBy('item.fuel', 'DESC')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countItemsInLocation(User $user, int $location)
    {
        if(!LocationEnum::isAValue($location))
            throw new \InvalidArgumentException('$location is not a valid LocationEnum value.');

        return (int)$this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:location')
            ->setParameter('user', $user)
            ->setParameter('location', $location)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
