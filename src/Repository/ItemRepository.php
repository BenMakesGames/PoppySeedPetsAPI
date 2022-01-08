<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Model\ItemQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findOneByName(string $itemName): Item
    {
        $item = $this->findOneBy([ 'name' => $itemName ]);

        if(!$item) throw new \InvalidArgumentException('There is no item called ' . $itemName . '.');

        return $item;
    }

    /**
     * @return Item[]
     */
    public function findOneForSpecialTraderOffer(int $seed): array
    {
        $count = $this->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->andWhere('i.recycleValue > 0')
            ->andWhere('i.treasure IS NULL')
            ->andWhere('i.fuel = 0')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $random = ($seed * 338959) % ($count - 1);

        return $this->createQueryBuilder('i')
            ->andWhere('i.recycleValue > 0')
            ->andWhere('i.treasure IS NULL')
            ->andWhere('i.fuel = 0')
            ->orderBy('i.id', 'ASC')
            ->setFirstResult($random)
            ->setMaxResults(2)
            ->getQuery()
            ->execute()
        ;
    }
}
