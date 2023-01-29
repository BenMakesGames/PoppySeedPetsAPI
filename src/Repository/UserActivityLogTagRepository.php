<?php

namespace App\Repository;

use App\Entity\UserActivityLogTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserActivityLogTag>
 *
 * @method UserActivityLogTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivityLogTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivityLogTag[]    findAll()
 * @method UserActivityLogTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivityLogTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivityLogTag::class);
    }

    public function add(UserActivityLogTag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserActivityLogTag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string[] $names
     * @return UserActivityLogTag[]
     */
    public function findByNames(array $names): array
    {
        return $this->findBy([ 'title' => $names ]);
    }
}
