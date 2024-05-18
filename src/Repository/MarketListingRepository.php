<?php

namespace App\Repository;

use App\Entity\Enchantment;
use App\Entity\Item;
use App\Entity\MarketListing;
use App\Entity\Spice;
use App\Functions\InventoryModifierFunctions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketListing>
 *
 * @method MarketListing|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketListing|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketListing[]    findAll()
 * @method MarketListing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class MarketListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketListing::class);
    }

    public function upsertLowestPriceForItem(Item $item, ?int $lowestPrice)
    {
        if($lowestPrice != null && $lowestPrice <= 0)
            throw new \InvalidArgumentException('Lowest price must be null or greater than 0.');

        $existingRecord = $this->findOneBy([
            'item' => $item,
        ]);

        if($existingRecord)
        {
            if($lowestPrice == null)
                $this->_em->remove($existingRecord);
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

        $this->_em->persist($newRecord);
    }

    public function findMarketListingForItem(int $itemId): ?MarketListing
    {
        $qb = $this->createQueryBuilder('ml')
            ->andWhere('ml.item = :itemId')
            ->setParameter('itemId', $itemId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
