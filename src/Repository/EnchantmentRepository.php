<?php

namespace App\Repository;

use App\Entity\Enchantment;
use App\Exceptions\PSPNotFoundException;
use App\Functions\CacheHelpers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Enchantment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enchantment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enchantment[]    findAll()
 * @method Enchantment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class EnchantmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enchantment::class);
    }

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
