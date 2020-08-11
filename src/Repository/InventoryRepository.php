<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
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

    public function userHasAnyOneOf(User $owner, array $itemNames, array $locationsToCheck = Inventory::CONSUMABLE_LOCATIONS): bool
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.name IN (:itemNames)')
            ->setParameter('user', $owner)
            ->setParameter('consumableLocations', $locationsToCheck)
            ->setParameter('itemNames', $itemNames)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
            > 0 // <-- this part is really important :P
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

    /**
     * @throws EnumInvalidValueException
     */
    public function countItemsInLocation(User $user, int $location)
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
