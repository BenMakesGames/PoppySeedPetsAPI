<?php

namespace App\Repository;

use App\Entity\Letter;
use App\Enum\EnumInvalidValueException;
use App\Enum\LetterSenderEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Letter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Letter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Letter[]    findAll()
 * @method Letter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class LetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Letter::class);
    }

    public function getNumberOfLettersFromSender(string $sender): int
    {
        if(!LetterSenderEnum::isAValue($sender))
            throw new EnumInvalidValueException(LetterSenderEnum::class, $sender);

        return (int)$this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.sender = :sender')
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findBySenderIndex(string $sender, int $index): ?Letter
    {
        if(!LetterSenderEnum::isAValue($sender))
            throw new EnumInvalidValueException(LetterSenderEnum::class, $sender);

        $results = $this->findBy([ 'sender' => $sender ], [ 'id' => 'ASC' ], 1, $index);

        return count($results) === 0 ? null : $results[0];
    }

    // /**
    //  * @return Letter[] Returns an array of Letter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Letter
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
