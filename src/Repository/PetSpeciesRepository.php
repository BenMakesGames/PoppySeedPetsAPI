<?php

namespace App\Repository;

use App\Entity\PetSpecies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetSpecies|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetSpecies|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetSpecies[]    findAll()
 * @method PetSpecies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class PetSpeciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetSpecies::class);
    }

}
