<?php

namespace App\Repository;

use App\Entity\Enchantment;
use App\Entity\Item;
use App\Entity\MarketListing;
use App\Entity\Spice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketListing>
 *
 * @method MarketListing|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketListing|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketListing[]    findAll()
 * @method MarketListing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarketListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketListing::class);
    }

    public function add(MarketListing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarketListing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function upsertLowestPriceForItem(Item $item, ?Enchantment $enchantment, ?Spice $spice, ?int $lowestPrice)
    {
        if($lowestPrice != null && $lowestPrice <= 0)
            throw new \InvalidArgumentException('Lowest price must be null or greater than 0.');

        $existingRecord = $this->findOneBy([
            'item' => $item,
            'enchantment' => $enchantment,
            'spice' => $spice,
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
            ->setEnchantment($enchantment)
            ->setSpice($spice)
            ->setMinimumSellPrice($lowestPrice)
        ;

        $this->_em->persist($newRecord);
    }
}
