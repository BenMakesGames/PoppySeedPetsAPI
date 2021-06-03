<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Model\ItemQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * @return Inventory[]
     */
    public function findTreasures(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.treasure', 'treasure')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.treasure IS NOT NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return Inventory[]
     */
    public function findTreasuresById(User $user, array $inventoryIds): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.treasure', 'treasure')
            ->andWhere('i.id IN (:inventoryIds)')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.treasure IS NOT NULL')
            ->setParameter('inventoryIds', $inventoryIds)
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return Inventory[]
     */
    public function findHollowEarthTiles(User $owner, array $types): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.hollowEarthTileCard', 'tileCard')
            ->leftJoin('tileCard.type', 'type')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.hollowEarthTileCard IS NOT NULL')
            ->andWhere('type.name IN (:allowedTypes)')
            ->setParameter('user', $owner->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->setParameter('allowedTypes', $types)
            ->getQuery()
            ->execute()
        ;
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

    public function findAnyOneFromItemGroup(User $owner, string $itemGroupName, array $locationsToCheck = Inventory::CONSUMABLE_LOCATIONS): ?Inventory
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->join('item.itemGroups', 'g')
            ->andWhere('g.name = :itemGroupName')
            ->setParameter('user', $owner)
            ->setParameter('consumableLocations', $locationsToCheck)
            ->setParameter('itemGroupName', $itemGroupName)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Inventory[]
     */
    public function findFertilizers(User $user, ?array $inventoryIds = null)
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location = :home')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fertilizer>0')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
        ;

        if($inventoryIds)
        {
            $qb
                ->andWhere('i.id IN (:inventoryIds)')
                ->setParameter('inventoryIds', $inventoryIds)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inventory[]
     */
    public function findFuel(User $user, ?array $inventoryIds = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:home)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fuel>0')
            ->addOrderBy('item.fuel', 'DESC')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
        ;

        if($inventoryIds)
        {
            $qb
                ->andWhere('i.id IN (:inventoryIds)')
                ->setParameter('inventoryIds', $inventoryIds)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function countItemsInLocation(User $user, int $location): int
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

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

    /**
     * @return ItemQuantity[]
     */
    public function getInventoryQuantities(User $user, int $location, $indexBy = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->from(Inventory::class, 'inventory')
            ->select('item,COUNT(inventory.id) AS quantity')
            ->leftJoin(Item::class, 'item', 'WITH', 'inventory.item = item.id')
            ->andWhere('inventory.owner=:user')
            ->andwhere('inventory.location=:location')
            ->groupBy('item.id')
            ->setParameter('user', $user->getId())
            ->setParameter('location', $location)
        ;

        $results = $query->getQuery()->execute();

        $quantities = [];

        foreach($results as $result)
        {
            $quantity = new ItemQuantity();
            $quantity->item = $result[0];
            $quantity->quantity = (int)$result['quantity'];

            if($indexBy)
            {
                $getter = 'get' . $indexBy;
                $quantities[$quantity->item->$getter()] = $quantity;
            }
            else
                $quantities[] = $quantity;
        }

        return $quantities;
    }
}
