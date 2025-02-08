<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\PetSpecies;
use App\Entity\User;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private EntityRepository $repository;
    private ?User $user;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(PetSpecies::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 's.nameSort' => 'asc' ], // first one is the default
                'id' => [ 's.id' => 'asc' ],
            ],
            [
                'name' => $this->filterName(...),
                'family' => $this->filterFamily(...),
                'hasPet' => $this->filterHasPet(...),
                'hasDiscovered' => $this->filterHasDiscovered(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('s');
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function filterHasPet(QueryBuilder $qb, $value): void
    {
        if(!$this->user || $value === null)
            return;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('s.id NOT IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');
        else
            $qb->andWhere('s.id IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');

        $qb->setParameter('user', $this->user);
    }

    public function filterHasDiscovered(QueryBuilder $qb, $value): void
    {
        if(!$this->user || $value === null)
            return;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('s.id NOT IN (SELECT IDENTITY(collected.species) FROM App\Entity\UserSpeciesCollected collected WHERE collected.user=:user)');
        else
            $qb->andWhere('s.id IN (SELECT IDENTITY(collected.species) FROM App\Entity\UserSpeciesCollected collected WHERE collected.user=:user)');

        $qb->setParameter('user', $this->user);
    }

    public function filterName(QueryBuilder $qb, $value): void
    {
        if(!$value)
            return;

        $qb
            ->andWhere('s.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($value) . '%')
        ;
    }

    public function filterFamily(QueryBuilder $qb, $value): void
    {
        $qb
            ->andWhere('s.family=:family')
            ->setParameter('family', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
