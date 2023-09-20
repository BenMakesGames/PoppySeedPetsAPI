<?php

namespace App\Repository;

use App\Entity\Enchantment;
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

    /**
     * @deprecated Use static EnchantmentRepository::findOneByName(...) instead
     */
    public function deprecatedFindOneByName(string $name): ?Enchantment
    {
        return $this->findOneBy([ 'name' => $name ]);
    }

    public static function findOneByName(EntityManagerInterface $em, string $name): ?Enchantment
    {
        return $em->getRepository(Enchantment::class)->findOneBy([ 'name' => $name ]);
    }
}
