<?php
namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\RelationshipEnum;
use App\Enum\StatusEffectEnum;
use App\Service\IRandom;
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
