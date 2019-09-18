<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Model\ItemQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
