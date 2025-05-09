<?php
declare(strict_types = 1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use Doctrine\ORM\EntityManagerInterface;

class FireplaceService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return Inventory[]
     */
    public function findFuel(User $user, ?array $inventoryIds = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
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
}