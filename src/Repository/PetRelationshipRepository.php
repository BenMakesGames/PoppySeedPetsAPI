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
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('pet.id=:petId')
            ->andWhere('friend.food + friend.alcohol + friend.junk > 0')
            ->andWhere('r.currentRelationship NOT IN (:excludedRelationshipTypes)')
            ->andWhere('friend.socialEnergy >= :minimumFriendSocialEnergy')
            ->setParameter('petId', $pet->getId())
            ->setParameter('excludedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
            ->setParameter('minimumFriendSocialEnergy', PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 2)
        ;

        return $qb->getQuery()->execute();
    }
}
