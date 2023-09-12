<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MonthlyStoryAdventure|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyStoryAdventure|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyStoryAdventure[]    findAll()
 * @method MonthlyStoryAdventure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class MonthlyStoryAdventureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyStoryAdventure::class);
    }
}
