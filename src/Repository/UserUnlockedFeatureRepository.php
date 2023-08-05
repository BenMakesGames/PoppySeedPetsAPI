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

    private $createdThisRequest = [];

    public function create(User $user, string $feature)
    {
        if(in_array($feature, $this->createdThisRequest))
            return;

        $this->createdThisRequest[] = $feature;

        $entity = (new UserUnlockedFeature())
            ->setUser($user)
            ->setFeature($feature)
        ;

        $user->addUnlockedFeature($entity);

        $this->getEntityManager()->persist($entity);
    }
}
