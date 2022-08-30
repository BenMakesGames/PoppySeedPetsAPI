<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MonthlyStoryAdventureStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyStoryAdventureStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyStoryAdventureStep[]    findAll()
 * @method MonthlyStoryAdventureStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlyStoryAdventureStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyStoryAdventureStep::class);
    }

    /**
     * @param UserMonthlyStoryAdventureStepCompleted[] $completed
     */
    public function findAvailable(User $user, array $completed)
    {

    }
}
