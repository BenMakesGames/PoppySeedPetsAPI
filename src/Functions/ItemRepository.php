<?php

namespace App\Functions;

use App\Entity\Item;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final class ItemRepository
{
    public static function findByNames(EntityManagerInterface $em, array $itemNames): array
    {
        return array_map(fn(string $itemName) => self::findOneByName($em, $itemName), $itemNames);
    }

    public static function findOneByName(EntityManagerInterface $em, string $itemName): Item
    {
        $item = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->where('i.name=:name')
            ->setParameter('name', $itemName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('ItemRepository_FindOneByName_' . $itemName))
            ->getOneOrNullResult();

        if(!$item) throw new PSPNotFoundException('There is no item called ' . $itemName . '.');

        return $item;
    }

    public static function findOneById(EntityManagerInterface $em, int $itemId): Item
    {
        $item = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->where('i.id=:id')
            ->setParameter('id', $itemId)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('ItemRepository_FindOneById_' . $itemId))
            ->getOneOrNullResult();

        if(!$item) throw new PSPNotFoundException('There is no item #' . $itemId . '.');

        return $item;
    }

    public static function getIdByName(EntityManagerInterface $em, string $itemName): int
    {
        $itemId = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->select('i.id')
            ->where('i.name=:name')
            ->setParameter('name', $itemName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('ItemRepository_GetIdByName_' . $itemName))
            ->getSingleScalarResult();

        if(!$itemId)
            throw new PSPNotFoundException('There is no item called ' . $itemName . '.');

        return $itemId;
    }
}
