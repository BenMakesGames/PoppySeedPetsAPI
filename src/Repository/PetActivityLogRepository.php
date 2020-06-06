<?php

namespace App\Repository;

use App\Entity\PetActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLog[]    findAll()
 * @method PetActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
}
