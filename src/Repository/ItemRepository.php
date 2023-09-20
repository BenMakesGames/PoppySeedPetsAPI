<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Model\ItemQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * @deprecated Use static ItemRepository::findOneByName, instead
     */
    public function deprecatedFindOneByName(string $itemName): Item
    {
        $item = $this->createQueryBuilder('i')
            ->where('i.name=:name')
            ->setParameter('name', $itemName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, 'ItemRepository_FindOneByName_' . $itemName)
            ->getOneOrNullResult();

        if(!$item) throw new PSPNotFoundException('There is no item called ' . $itemName . '.');

        return $item;
    }

    public static function findOneByName(EntityManagerInterface $em, string $itemName): Item
    {
        $item = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->where('i.name=:name')
            ->setParameter('name', $itemName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, 'ItemRepository_FindOneByName_' . $itemName)
            ->getOneOrNullResult();

        if(!$item) throw new PSPNotFoundException('There is no item called ' . $itemName . '.');

        return $item;
    }

    public static function getIdByName(EntityManagerInterface $em, string $itemName): int
    {
        $itemId = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->select('i.id')
            ->where('i.name=:name')
            ->setParameter('name', $itemName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, 'ItemRepository_GetIdByName_' . $itemName)
            ->getSingleScalarResult();

        if(!$itemId)
            throw new PSPNotFoundException('There is no item called ' . $itemName . '.');

        return $itemId;
    }
}
