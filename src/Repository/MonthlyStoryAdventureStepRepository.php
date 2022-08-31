<?php

namespace App\Repository;

use App\Entity\MonthlyStoryAdventure;
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
     * @return MonthlyStoryAdventureStep[]
     */
    public function findAvailable(User $user, MonthlyStoryAdventure $adventure, array $completed): array
    {
        $qb = $this->createQueryBuilder('s');

        $completedSteps = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getStep(), $completed);
        $completedAdventureStepIds = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getId(), $completed);

        $qb = $qb
            ->andWhere('s.adventure = :adventure')
            ->andWhere($qb->expr()->orX('s.previousStep IS NULL', 's.previousStep IN (:completedSteps)'))
            ->andWhere('s.id NOT IN (:completedAdventureStepIds)')
            ->setParameter('adventure', $adventure)
            ->setParameter('completedSteps', $completedSteps)
            ->setParameter('completedAdventureStepIds', $completedAdventureStepIds)
        ;

        return $qb->getQuery()->execute();
    }
}
