<?php
namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\RelationshipEnum;
use App\Enum\StatusEffectEnum;
use App\Service\Squirrel3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pet[]    findAll()
 * @method Pet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
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
    public function findPetsEligibleForParkEvent(string $eventType, int $number): array
    {
        $today = new \DateTimeImmutable();

        $pets = $this->createQueryBuilder('p')
            //->join('p.skills', 's')
            ->leftJoin('p.statusEffects', 'statusEffects')
            ->andWhere('p.parkEventType=:eventType')
            ->andWhere('(p.lastParkEvent<:today OR p.lastParkEvent IS NULL)')
            ->andWhere('p.location=:home')
            ->andWhere('p.lastInteracted>=:twoDaysAgo')
            ->orderBy('p.parkEventOrder', 'ASC')
            ->setMaxResults($number)
            ->setParameter('eventType', $eventType)
            ->setParameter('home', PetLocationEnum::HOME)
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('twoDaysAgo', $today->modify('-48 hours')->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        return array_values(array_filter($pets, fn(Pet $pet) => !$pet->hasStatusEffect(StatusEffectEnum::WEREFORM)));
    }

    public static function getNumberAtHome(EntityManagerInterface $em, User $user): int
    {
        return $em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.owner=:owner')
            ->andWhere('p.location=:home')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getTotalOwned(User $user): int
    {
        return (int)$this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.owner=:owner')
            ->setParameter('owner', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findRandomTrickOrTreater(User $user): ?Pet
    {
        $squirrel3 = new Squirrel3();
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

        $offset = $squirrel3->rngNextInt(0, $numberOfPets - 1);

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

    public function findRandomCourier(Pet $except): ?Pet
    {
        $squirrel3 = new Squirrel3();
        $oneMonthAgo = (new \DateTimeImmutable())->modify('-1 month');

        $numberEligible = (int)$this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->join('p.owner', 'o')
            ->join('p.guildMembership', 'm')
            ->join('m.guild', 'g')
            ->andWhere('o.lastActivity>=:oneMonthAgo')
            ->andWhere('p.id!=:exceptPetId')
            ->andWhere('g.name=:correspondence')
            ->andWhere('m.level>=10')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->setParameter('exceptPetId', $except->getId())
            ->setParameter('correspondence', 'Correspondence')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($numberEligible === 0)
            return null;

        $petIndex = $squirrel3->rngNextInt(0, $numberEligible - 1);

        return $this->createQueryBuilder('p')
            ->join('p.owner', 'o')
            ->join('p.guildMembership', 'm')
            ->join('m.guild', 'g')
            ->andWhere('o.lastActivity>=:oneMonthAgo')
            ->andWhere('p.id!=:exceptPetId')
            ->andWhere('g.name=:correspondence')
            ->andWhere('m.level>=10')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->setParameter('exceptPetId', $except->getId())
            ->setParameter('correspondence', 'Correspondence')
            ->setFirstResult($petIndex)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function getNumberHavingSpecies(PetSpecies $petSpecies): int
    {
        return (int)$this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.species=:species')
            ->setParameter('species', $petSpecies)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
