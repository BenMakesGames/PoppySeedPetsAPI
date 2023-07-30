<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetQuest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetQuest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetQuest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetQuest[]    findAll()
 * @method PetQuest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetQuestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetQuest::class);
    }

    private $petQuestPerRequestCache = [];

    public function exists(Pet $pet, string $name): bool
    {
        $cacheKey = $pet->getId() . '-' . $name;

        if(array_key_exists($cacheKey, $this->petQuestPerRequestCache))
            return true;

        return $this->count([
            'pet' => $pet,
            'name' => $name,
        ]) > 0;
    }

    public function findOrCreate(Pet $pet, string $name, $default): PetQuest
    {
        $cacheKey = $pet->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, $this->petQuestPerRequestCache))
        {
            $record = $this->findOneBy([
                'pet' => $pet,
                'name' => $name,
            ]);

            if(!$record)
            {
                $record = (new PetQuest())
                    ->setPet($pet)
                    ->setName($name)
                    ->setValue($default)
                ;

                $this->getEntityManager()->persist($record);
            }

            $this->petQuestPerRequestCache[$cacheKey] = $record;
        }

        return $this->petQuestPerRequestCache[$cacheKey];
    }
}
