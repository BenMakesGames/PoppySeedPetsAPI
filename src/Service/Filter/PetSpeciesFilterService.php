<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\Filter;

use App\Entity\PetSpecies;
use App\Entity\User;
use App\Functions\CacheHelpers;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    /** @var EntityRepository<PetSpecies> */
    private EntityRepository $repository;
    private ?User $user;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(PetSpecies::class);

        $this->filterer = new Filterer(
            self::PageSize,
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

    public function filterHasPet(QueryBuilder $qb, mixed $value): void
    {
        if(!$this->user || $value === null)
            return;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('s.id NOT IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');
        else
            $qb->andWhere('s.id IN (SELECT IDENTITY(pet.species) FROM App\Entity\Pet pet WHERE pet.owner=:user)');

        $qb->setParameter('user', $this->user);
    }

    public function filterHasDiscovered(QueryBuilder $qb, mixed $value): void
    {
        if(!$this->user || $value === null)
            return;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('s.id NOT IN (SELECT IDENTITY(collected.species) FROM App\Entity\UserSpeciesCollected collected WHERE collected.user=:user)');
        else
            $qb->andWhere('s.id IN (SELECT IDENTITY(collected.species) FROM App\Entity\UserSpeciesCollected collected WHERE collected.user=:user)');

        $qb->setParameter('user', $this->user);
    }

    public function filterName(QueryBuilder $qb, mixed $value): void
    {
        if(!$value)
            return;

        $qb
            ->andWhere('s.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($value) . '%')
        ;
    }

    public function filterFamily(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('s.family=:family')
            ->setParameter('family', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb->enableResultCache(
            24 * 60 * 60,
            CacheHelpers::getCacheItemName(self::class . '_' . $cacheKey)
        );
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
