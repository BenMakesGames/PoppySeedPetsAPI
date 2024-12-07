<?php
namespace App\Repository;

use App\Entity\MonthlyStoryAdventure;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMonthlyStoryAdventureStepCompleted|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMonthlyStoryAdventureStepCompleted|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMonthlyStoryAdventureStepCompleted[]    findAll()
 * @method UserMonthlyStoryAdventureStepCompleted[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserMonthlyStoryAdventureStepCompletedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMonthlyStoryAdventureStepCompleted::class);
    }

    /**
     * @return UserMonthlyStoryAdventureStepCompleted[]
     */
    public function findComplete(User $user, MonthlyStoryAdventure $adventure): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.adventureStep', 's')
            ->andWhere('c.user = :user')
            ->andWhere('s.adventure = :adventure')
            ->setParameter('user', $user)
            ->setParameter('adventure', $adventure)
            ->getQuery()
            ->execute();
    }
}
