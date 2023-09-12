<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLog[]    findAll()
 * @method PetActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class PetActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetActivityLog::class);
    }

    /**
     * @return PetActivityLog[]
     */
    public function findUnreadForUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.pet', 'pet')
            ->andWhere('pet.owner = :user')
            ->andWhere('l.viewed = 0')
            ->setParameter('user', $user)

            ->getQuery()
            ->execute()
        ;
    }

    public function findLogsForPetByDate(Pet $pet, int $year, int $month): array
    {
        $firstDayOfMonth = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';

        // ... >_>
        if($month == 12)
            $firstDayOfNextMonth = ($year + 1) . '-01-01';
        else
            $firstDayOfNextMonth = $year . '-' . str_pad($month + 1, 2, '0', STR_PAD_LEFT) . '-01';

        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l) AS quantity,SUM(l.interestingness)/COUNT(l) AS averageInterestingness, DATE(l.createdOn) AS yearMonthDay')
            ->andWhere('l.pet = :pet')
            ->andWhere('l.createdOn >= :firstDayOfMonth')
            ->andWhere('l.createdOn < :firstDayOfNextMonth')
            ->addGroupBy('yearMonthDay')

            ->setParameter('pet', $pet)
            ->setParameter('firstDayOfMonth', $firstDayOfMonth)
            ->setParameter('firstDayOfNextMonth', $firstDayOfNextMonth)
        ;

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
    }
}
