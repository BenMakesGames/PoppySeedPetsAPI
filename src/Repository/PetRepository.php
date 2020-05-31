<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\User;
use App\Enum\RelationshipEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pet[]    findAll()
 * @method Pet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    /**
     * @return Pet[]
     */
    public function findParents(Pet $pet): array
    {
        $parents = $pet->getParents();

        if(count($parents) === 0)
            return [];

        return $this->createQueryBuilder('p')
            ->andWhere('p.id IN (:petParents)')
            ->setParameter('petParents', array_map(function(Pet $p) { return $p->getId(); }, $parents))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Pet[]
     */
    public function findSiblings(Pet $pet): array
    {
        $parents = $pet->getParents();

        if(count($parents) === 0)
            return [];

        return $this->createQueryBuilder('p')
            ->andWhere('p.mom IN (:petParents) OR p.dad IN (:petParents)')
            ->setParameter('petParents', array_map(function(Pet $p) { return $p->getId(); }, $parents))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Pet[]
     */
    public function findChildren(Pet $pet): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.mom=:pet OR p.dad=:pet')
            ->setParameter('pet', $pet->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Pet[]
     */
    public function findPetsEligibleForParkEvent(string $eventType, int $number)
    {
        $today = new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
            ->join('p.skills', 's')
            ->andWhere('p.parkEventType=:eventType')
            ->andWhere('(p.lastParkEvent<:today OR p.lastParkEvent IS NULL)')
            ->andWhere('p.inDaycare=0')
            ->orderBy('p.parkEventOrder', 'ASC')
            ->setMaxResults($number)
            ->setParameter('eventType', $eventType)
            ->setParameter('today', $today->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function getNumberAtHome(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.owner=:owner')
            ->andWhere('p.inDaycare=0')
            ->setParameter('owner', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getTotalOwned(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.owner=:owner')
            ->setParameter('owner', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getRoommates(Pet $pet)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.owner = :owner')
            ->andWhere('p.inDaycare = 0')
            ->andWhere('p.id != :thisPet')
            ->setParameter('owner', $pet->getOwner())
            ->setParameter('thisPet', $pet->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRandomTrickOrTreater(User $user): ?Pet
    {
        $oneDayAgo = (new \DateTimeImmutable())->modify('-24 hours');

        $numberOfPets = (int)$this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.tool IS NOT NULL')
            ->andWhere('p.hat IS NOT NULL')
            ->andWhere('p.lastInteracted >= :oneDayAgo')
            ->andWhere('p.owner != :user')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($numberOfPets === 0)
            return null;

        $offset = mt_rand(0, $numberOfPets - 1);

        return $this->createQueryBuilder('p')
            ->andWhere('p.tool IS NOT NULL')
            ->andWhere('p.hat IS NOT NULL')
            ->andWhere('p.lastInteracted >= :oneDayAgo')
            ->andWhere('p.owner != :user')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('user', $user->getId())
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * @return Pet[]
     */
    public function findFriendsWithFewGroups(Pet $pet): array
    {
        $friendlyRelationships = [
            RelationshipEnum::FRIEND,
            RelationshipEnum::BFF,
            RelationshipEnum::FWB,
            RelationshipEnum::MATE
        ];

        $relationshipsWithFewGroups = array_filter(
            $pet->getPetRelationships()->toArray(),
            function(PetRelationship $r) use($friendlyRelationships, $pet)
            {
                $otherSide = $r->getRelationship()->getRelationshipWith($pet);

                return
                    //
                    $r->getCurrentRelationship() !== RelationshipEnum::BROKE_UP &&

                    // as long as both pets WANT a friendly relationship, they'll do this
                    $otherSide &&
                    in_array($otherSide->getRelationshipGoal(), $friendlyRelationships) &&
                    in_array($r->getRelationshipGoal(), $friendlyRelationships) &&

                    // the pets involved must not already have too many group commitments
                    $r->getRelationship()->getGroups()->count() < $r->getRelationship()->getMaximumGroups()
                ;
            }
        );

        return array_map(function(PetRelationship $p) { return $p->getRelationship(); }, $relationshipsWithFewGroups);
    }
}
