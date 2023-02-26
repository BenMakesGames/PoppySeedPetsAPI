<?php

namespace App\Repository;

use App\Entity\MarketListing;
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

    public function findLowestPriceFor(int $itemId, ?int $enchantmentId, ?int $spiceId): ?int
    {
        $qb = $this->createQueryBuilder('ml')
            ->select('ml.minimumSellPrice')
            ->where('ml.item = :item')
            ->setParameter('item', $itemId);

        if ($enchantmentId)
        {
            $qb
                ->andWhere('ml.enchantment = :enchantment')
                ->setParameter('enchantment', $enchantmentId);
        }
        else
            $qb->andWhere('ml.enchantment IS NULL');

        if ($spiceId)
        {
            $qb
                ->andWhere('ml.spice = :spice')
                ->setParameter('spice', $spiceId);
        }
        else
            $qb->andWhere('ml.spice IS NULL');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
