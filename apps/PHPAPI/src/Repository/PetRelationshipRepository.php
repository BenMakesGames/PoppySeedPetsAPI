<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Enum\StatusEffectEnum;
use App\Service\PetExperienceService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetRelationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetRelationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetRelationship[]    findAll()
 * @method PetRelationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class PetRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
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
            ->leftJoin('friend.houseTime', 'friendHouseTime')
            ->leftJoin('friend.statusEffects', 'friendStatusEffects')
            ->andWhere('pet.id=:petId')
            ->andWhere('friend.food + friend.alcohol + friend.junk > 0')
            ->andWhere('r.currentRelationship NOT IN (:excludedRelationshipTypes)')
            ->andWhere('friendHouseTime.socialEnergy >= :minimumFriendSocialEnergy')
            ->addOrderBy('r.commitment', 'DESC')
            ->setMaxResults($maxFriendsToConsider)
            ->setParameter('petId', $pet->getId())
            ->setParameter('excludedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
            ->setParameter('minimumFriendSocialEnergy', (PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 3) / 2)
        ;

        $friends = $qb->getQuery()->execute();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            // pets in Wereform only hang out with other pets in Wereform
            $friends = array_values(array_filter($friends, function(PetRelationship $r) {
                return $r->getRelationship()->hasStatusEffect(StatusEffectEnum::WEREFORM);
            }));
        }
        else
        {
            // pets NOT in Wereform only hang out with other pets NOT in Wereform
            $friends = array_values(array_filter($friends, function(PetRelationship $r) {
                return !$r->getRelationship()->hasStatusEffect(StatusEffectEnum::WEREFORM);
            }));
        }

        return $friends;
    }

    /**
     * @return PetRelationship[]
     */
    public function getDislikedRelationshipsWithCommitment(Pet $pet): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.currentRelationship IN (:dislikedRelationshipTypes)')
            ->andWhere('r.commitment>0')
            ->andWhere('pet.id=:petId')
            ->setParameter('petId', $pet->getId())
            ->setParameter('dislikedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
        ;

        $results = $qb->getQuery()->execute();

        return $results;
    }

    /**
     * @return PetRelationship[]
     */
    public function getDislikedRelationships(Pet $pet): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.currentRelationship IN (:dislikedRelationshipTypes)')
            ->andWhere('pet.id=:petId')
            ->setParameter('petId', $pet->getId())
            ->setParameter('dislikedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
        ;

        $results = $qb->getQuery()->execute();

        return $results;
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
