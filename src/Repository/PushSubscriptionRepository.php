<?php

namespace App\Repository;

use App\Entity\PushSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PushSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method PushSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method PushSubscription[]    findAll()
 * @method PushSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PushSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PushSubscription::class);
    }
}
