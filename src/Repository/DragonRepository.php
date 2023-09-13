<?php

namespace App\Repository;

use App\Entity\Dragon;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dragon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dragon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dragon[]    findAll()
 * @method Dragon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class DragonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dragon::class);
    }

    public function findWhelp(User $user): ?Dragon
    {
        return $this->findOneBy([
            'owner' => $user,
            'isAdult' => false
        ]);
    }

    public function findAdult(User $user): ?Dragon
    {
        return $this->findOneBy([
            'owner' => $user,
            'isAdult' => true
        ]);
    }
}
