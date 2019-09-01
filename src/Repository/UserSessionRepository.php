<?php

namespace App\Repository;

use App\Entity\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSession[]    findAll()
 * @method UserSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    public function findOneBySessionId(string $sessionId): ?UserSession
    {
        return $this->findOneBy([ 'sessionId' => $sessionId ]);
    }
}
