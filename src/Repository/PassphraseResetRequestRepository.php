<?php

namespace App\Repository;

use App\Entity\PassphraseResetRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PassphraseResetRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PassphraseResetRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PassphraseResetRequest[]    findAll()
 * @method PassphraseResetRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PassphraseResetRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PassphraseResetRequest::class);
    }
}
