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

namespace App\Functions;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InventoryHelpers
{
    /**
     * Removes an enchantment (bonus) from an inventory item.
     */
    public static function removeEnchantment(EntityManagerInterface $em, Inventory $inventory): void
    {
        if(!$inventory->getEnchantmentData())
            return;

        $em->remove($inventory->getEnchantmentData());
        $inventory->setEnchantment(null);
    }

    public static function findOneToConsume(EntityManagerInterface $em, User $owner, string $itemName): ?Inventory
    {
        return $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.name=:itemName')
            ->setParameter('user', $owner)
            ->setParameter('consumableLocations', Inventory::ConsumableLocations)
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
    public static function findAnyOneFromItemGroup(EntityManagerInterface $em, User $owner, string $itemGroupName, array $locationsToCheck = Inventory::ConsumableLocations): ?Inventory
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
}
