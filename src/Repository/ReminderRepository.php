<?php

namespace App\Repository;

use App\Entity\Reminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Reminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reminder[]    findAll()
 * @method Reminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReminderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reminder::class);
    }

    /**
     * @return Reminder[]
     */
    public function findReadyReminders()
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('r')
            ->andWhere('r.nextReminder <= :now')
            ->orderBy('r.nextReminder', 'ASC')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute()
        ;
    }
}
