<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\PetSpeciesRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private PetSpeciesRepository $repository;
    private ?User $user;

    public function __construct(PetSpeciesRepository $petSpeciesRepository)
    {
        $this->repository = $petSpeciesRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 's.nameSort' => 'asc' ], // first one is the default
                'id' => [ 's.id' => 'asc' ],
            ],
            [
                'name' => [ $this, 'filterName' ],
                'family' => [ $this, 'filterFamily' ],
                'canTransmigrate' => [ $this, 'filterCanTransmigrate' ],
                'hasPet' => [ $this, 'filterHasPet' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('s');
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function filterHasPet(QueryBuilder $qb, $value)
    {
        if(!$this->user || $value === null)
            return;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('s.id NOT IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');
        else
            $qb->andWhere('s.id IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');

        $qb->setParameter('user', $this->user);
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        if(!$value)
            return;

        $qb
            ->andWhere('s.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterFamily(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('s.family LIKE :family')
            ->setParameter('family', $value)
        ;
    }

    public function filterCanTransmigrate(QueryBuilder $qb, $value)
    {
        if($value)
            $qb->andWhere('(s.id<=16 OR s.availableFromBreeding=1 OR s.availableFromPetShelter=1)');
        else
            $qb->andWhere('s.id>16 AND s.availableFromBreeding=0 AND s.availableFromPetShelter=0');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
