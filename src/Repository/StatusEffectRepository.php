<?php

namespace App\Repository;

use App\Entity\StatusEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StatusEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusEffect[]    findAll()
 * @method StatusEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusEffectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StatusEffect::class);
    }

    // /**
    //  * @return StatusEffect[] Returns an array of StatusEffect objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StatusEffect
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
