<?php

namespace App\Repository;

use App\Entity\PetSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PetSkill|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetSkill|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetSkill[]    findAll()
 * @method PetSkill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetSkillRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PetSkill::class);
    }

    // /**
    //  * @return PetSkill[] Returns an array of PetSkill objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PetSkill
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
