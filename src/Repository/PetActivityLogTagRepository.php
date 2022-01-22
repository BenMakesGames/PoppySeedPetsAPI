<?php

namespace App\Repository;

use App\Entity\PetActivityLogTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetActivityLogTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLogTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLogTag[]    findAll()
 * @method PetActivityLogTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetActivityLogTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetActivityLogTag::class);
    }

    /**
     * @param string[] $names
     * @return PetActivityLogTag[]
     */
    public function findByNames(array $names): array
    {
        return $this->findBy([ 'title' => $names ]);
    }
}
