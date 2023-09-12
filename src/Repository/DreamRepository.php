<?php

namespace App\Repository;

use App\Entity\Dream;
use App\Entity\HollowEarthTile;
use App\Service\IRandom;
use App\Service\Squirrel3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dream|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dream|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dream[]    findAll()
 * @method Dream[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class DreamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dream::class);
    }

    public function findRandom(IRandom $rng): Dream
    {
        $numberOfDreams = (int)$this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $offset = $rng->rngNextInt(0, $numberOfDreams - 1);

        return $this->createQueryBuilder('d')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
