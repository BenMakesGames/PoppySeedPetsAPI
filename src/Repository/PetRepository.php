<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pet[]    findAll()
 * @method Pet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    public function findPetsEligibleForParkEvent(string $eventType, int $number)
    {
        $today = new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
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
}
