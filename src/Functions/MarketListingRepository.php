<?php

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
