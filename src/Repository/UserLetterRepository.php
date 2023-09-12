<?php

namespace App\Repository;

use App\Entity\Letter;
use App\Entity\User;
use App\Entity\UserLetter;
use App\Enum\EnumInvalidValueException;
use App\Enum\LetterSenderEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLetter|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLetter|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLetter[]    findAll()
 * @method UserLetter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class UserLetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLetter::class);
    }

    public function getNumberUnread(User $user)
    {
        return (int)$this->createQueryBuilder('ul')
            ->select('COUNT(ul.id)')
            ->andWhere('ul.user=:userId')
            ->andWhere('ul.isRead=0')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getNumberOfLettersFromSender(User $user, string $sender)
    {
        if(!LetterSenderEnum::isAValue($sender))
            throw new EnumInvalidValueException(LetterSenderEnum::class, $sender);

        return (int)$this->createQueryBuilder('ul')
            ->select('COUNT(ul.id)')
            ->leftJoin('ul.letter', 'l')
            ->andWhere('ul.user = :userId')
            ->andWhere('l.sender = :sender')
            ->setParameter('userId', $user->getId())
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return UserLetter[]
     */
    public function findFromSender(User $user, string $sender): array
    {
        if(!LetterSenderEnum::isAValue($sender))
            throw new EnumInvalidValueException(LetterSenderEnum::class, $sender);

        return $this->createQueryBuilder('ul')
            ->leftJoin('ul.letter', 'l')
            ->andWhere('ul.user = :userId')
            ->andWhere('l.sender = :sender')
            ->setParameter('userId', $user->getId())
            ->setParameter('sender', $sender)
            ->getQuery()
            ->execute()
        ;
    }

    // /**
    //  * @return UserLetter[] Returns an array of UserLetter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserLetter
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
