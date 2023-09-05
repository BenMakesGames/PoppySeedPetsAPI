<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserSpeciesCollectedRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserSpeciesCollectedFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private UserSpeciesCollectedRepository $repository;

    public function __construct(UserSpeciesCollectedRepository $petSpeciesRepository)
    {
        $this->repository = $petSpeciesRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'discoveredOn' => [ 'd.discoveredOn' => 'desc' ], // first one is the default
                'speciesName' => [ 'd.species.nameSort' => 'asc' ],
                'id' => [ 'species.species.id' => 'asc' ],
            ],
            [
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('d')
            ->innerJoin('d.species', 'species');
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('d.user = :userId')
            ->setParameter('userId', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
