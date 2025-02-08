<?php
declare(strict_types=1);

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
 * @deprecated
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
    public function findAvailable(MonthlyStoryAdventure $adventure, array $completed): array
    {
        $qb = $this->createQueryBuilder('s');

        $completedSteps = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getStep(), $completed);
        $completedAdventureStepIds = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getId(), $completed);

        $qb = $qb
            ->andWhere('s.adventure = :adventure')
            ->setParameter('adventure', $adventure)
        ;

        if(count($completedSteps) > 0)
        {
            $qb = $qb
                ->andWhere($qb->expr()->orX('s.previousStep IS NULL', 's.previousStep IN (:completedSteps)'))
                ->setParameter('completedSteps', $completedSteps)
            ;
        }
        else
        {
            $qb = $qb->andWhere('s.previousStep IS NULL');
        }

        if(count($completedAdventureStepIds) > 0)
        {
            $qb = $qb
                ->andWhere('s.id NOT IN (:completedAdventureStepIds)')
                ->setParameter('completedAdventureStepIds', $completedAdventureStepIds)
            ;
        }

        return $qb->getQuery()->execute();
    }
}
