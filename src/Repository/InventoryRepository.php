<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Model\ItemQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public static function findOneToConsume(EntityManagerInterface $em, User $owner, string $itemName): ?Inventory
    {
        return $em->getRepository(Inventory::class)->createQueryBuilder('i')
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

    /**
     * @param int[] $locationsToCheck
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public static function findAnyOneFromItemGroup(EntityManagerInterface $em, User $owner, string $itemGroupName, array $locationsToCheck = Inventory::CONSUMABLE_LOCATIONS): ?Inventory
    {
        return $em->getRepository(Inventory::class)->createQueryBuilder('i')
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
     * @param int[] $inventoryIds
     * @return Inventory[]
     */
    public static function findFertilizers(EntityManagerInterface $em, User $user, ?array $inventoryIds = null)
    {
        $qb = $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location = :home')
            ->leftJoin('i.item', 'item')
            ->leftJoin('i.spice', 'spice')
            ->leftJoin('spice.effects', 'effects')

            // has positive fertilizer - DON'T care about spices or whatever, we definitely want to show it
            // has 0 or negative fertilizer - only show if it has food or love greater than negative fertilizer (food + love exceeds badness of fertilizer)
            ->andWhere('item.fertilizer > 0 OR (effects.food + effects.love > -item.fertilizer)')

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
    public static function countItemsInLocation(EntityManagerInterface $em, User $user, int $location): int
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

        return (int)$em->getRepository(Inventory::class)->createQueryBuilder('i')
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
