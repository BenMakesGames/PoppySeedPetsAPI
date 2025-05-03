<?php
declare(strict_types = 1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use Doctrine\ORM\EntityManagerInterface;

class FireplaceService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return Inventory[]
     */
    public function findFuel(User $user, ?array $inventoryIds = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:home)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.fuel>0')
            ->addOrderBy('item.fuel', 'DESC')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
        ;

        if($inventoryIds)
        {
            $qb
                ->andWhere('i.id IN (:inventoryIds)')
                ->setParameter('inventoryIds', $inventoryIds)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

}