<?php

namespace App\Repository;

use App\Entity\Enchantment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @deprecated
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy([ 'name' => $name ]);
    }
}
