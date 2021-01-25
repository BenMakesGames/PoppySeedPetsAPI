<?php

namespace App\Repository;

use App\Entity\DailyStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailyStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyStats[]    findAll()
 * @method DailyStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyStats::class);
    }
}
