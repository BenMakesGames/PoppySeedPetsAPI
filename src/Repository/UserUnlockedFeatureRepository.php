<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserUnlockedFeature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserUnlockedFeature>
 *
 * @method UserUnlockedFeature|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUnlockedFeature|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUnlockedFeature[]    findAll()
 * @method UserUnlockedFeature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUnlockedFeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserUnlockedFeature::class);
    }

    public function add(UserUnlockedFeature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserUnlockedFeature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function create(User $user, string $feature): UserUnlockedFeature
    {
        $entity = (new UserUnlockedFeature())
            ->setUser($user)
            ->setFeature($feature)
        ;

        $this->add($entity, true);

        return $entity;
    }
}
