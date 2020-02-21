<?php

namespace App\Repository;

use App\Entity\PetRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetRelationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetRelationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetRelationship[]    findAll()
 * @method PetRelationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetRelationship::class);
    }
}
