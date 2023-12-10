<?php

namespace App\Functions;

use App\Entity\Enchantment;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class EnchantmentRepository
{
    public static function findOneByName(EntityManagerInterface $em, string $name): ?Enchantment
    {
        $enchantment = $em->getRepository(Enchantment::class)->createQueryBuilder('e')
            ->where('e.name=:name')
            ->setParameter('name', $name)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('EnchantmentRepository_FindOneByName_' . $name))
            ->getOneOrNullResult();

        if(!$enchantment) throw new PSPNotFoundException('There is no enchantment called ' . $name . '.');

        return $enchantment;
    }
}
