<?php

namespace App\Repository;

use App\Entity\ItemGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemGroup[]    findAll()
 * @method ItemGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class ItemGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemGroup::class);
    }

    public function findOneByName(string $name): ItemGroup
    {
        return $this->findOneBy([ 'name' => $name ]);
    }
}
