<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\Spice;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class SpiceRepository
{
    public static function findOneByName(EntityManagerInterface $em, string $spiceName): ?Spice
    {
        $spice = $em->getRepository(Spice::class)->createQueryBuilder('s')
            ->where('s.name=:name')
            ->setParameter('name', $spiceName)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName('SpiceRepository_FindOneByName_' . $spiceName))
            ->getOneOrNullResult();

        if(!$spice) throw new PSPNotFoundException('There is no spice called ' . $spiceName . '.');

        return $spice;
    }
}
