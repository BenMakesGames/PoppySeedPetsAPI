<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserQuest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserQuest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserQuest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserQuest[]    findAll()
 * @method UserQuest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserQuestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserQuest::class);
    }

    private $userQuestPerRequestCache = [];

    public function findOrCreate(User $user, string $name, $default): UserQuest
    {
        $cacheKey = $user->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, $this->userQuestPerRequestCache))
        {
            $record = $this->findOneBy([
                'user' => $user,
                'name' => $name,
            ]);

            if(!$record)
            {
                $record = (new UserQuest())
                    ->setUser($user)
                    ->setName($name)
                    ->setValue($default)
                ;

                $this->getEntityManager()->persist($record);
            }

            $this->userQuestPerRequestCache[$cacheKey] = $record;
        }

        return $this->userQuestPerRequestCache[$cacheKey];
    }
}
