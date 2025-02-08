<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\UserSpeciesCollected;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserSpeciesCollectedFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserSpeciesCollected::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'discoveredon' => [ 'd.discoveredOn' => 'desc' ], // first one is the default
                'speciesname' => [ 'species.nameSort' => 'asc' ],
                'id' => [ 'species.id' => 'asc' ],
            ],
            [
                'user' => $this->filterUser(...),
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
            ->setParameter('userId', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
