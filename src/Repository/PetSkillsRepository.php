<?php

namespace App\Repository;

use App\Entity\PetSkills;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetSkills|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetSkills|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetSkills[]    findAll()
 * @method PetSkills[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetSkillsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetSkills::class);
    }
}
