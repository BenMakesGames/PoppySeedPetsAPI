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

use App\Entity\Item;
use App\Entity\MarketListing;
use Doctrine\ORM\EntityManagerInterface;

class MarketListingRepository
{
    public static function upsertLowestPriceForItem(EntityManagerInterface $em, Item $item, ?int $lowestPrice): void
    {
        if($lowestPrice != null && $lowestPrice <= 0)
            throw new \InvalidArgumentException('Lowest price must be null or greater than 0.');

        $existingRecord = $em->getRepository(MarketListing::class)->findOneBy([
            'item' => $item,
        ]);

        if($existingRecord)
        {
            if($lowestPrice == null)
                $em->remove($existingRecord);
            else
                $existingRecord->setMinimumSellPrice($lowestPrice);

            return;
        }

        if($lowestPrice == null)
            return;

        $newRecord = (new MarketListing())
            ->setItem($item)
            ->setMinimumSellPrice($lowestPrice)
        ;

        $em->persist($newRecord);
    }

    public static function findMarketListingForItem(EntityManagerInterface $em, int $itemId): ?MarketListing
    {
        $qb = $em->getRepository(MarketListing::class)->createQueryBuilder('ml')
            ->andWhere('ml.item = :itemId')
            ->setParameter('itemId', $itemId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
