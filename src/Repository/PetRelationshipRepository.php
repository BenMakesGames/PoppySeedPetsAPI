<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Service\PetExperienceService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetRelationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetRelationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetRelationship[]    findAll()
 * @method PetRelationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetRelationship::class);
    }

    /**
     * @return PetRelationship[]
     */
    public function getRelationshipsToHangOutWith(Pet $pet): array
    {
        $maxFriendsToConsider = $pet->getMaximumFriends();

        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('pet.id=:petId')
            ->andWhere('friend.food + friend.alcohol + friend.junk > 0')
            ->andWhere('r.currentRelationship NOT IN (:excludedRelationshipTypes)')
            ->andWhere('friend.socialEnergy >= :minimumFriendSocialEnergy')
            ->addOrderBy('r.commitment', 'DESC')
            ->setMaxResults($maxFriendsToConsider)
            ->setParameter('petId', $pet->getId())
            ->setParameter('excludedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
            ->setParameter('minimumFriendSocialEnergy', floor(PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 3 / 2))
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return PetRelationship[]
     */
    public function getFriends(Pet $pet): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.pet=:pet')
            ->addOrderBy('r.commitment', 'DESC')
            ->setMaxResults($pet->getMaximumFriends())
            ->setParameter('pet', $pet)
        ;

        return $qb->getQuery()->execute();
    }

    public function countRelationships(Pet $pet, ?array $status = null): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->andWhere('r.pet=:pet')
            ->setParameter('pet', $pet)
        ;

        if($status !== null)
        {
            $qb = $qb
                ->andWhere('r.currentRelationship IN (:currentRelationship)')
                ->setParameter('currentRelationship', $status)
            ;
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
