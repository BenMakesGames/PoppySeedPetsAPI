<?php
namespace App\Service\Filter;

use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetRelationshipFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(PetRelationshipRepository $petRelationshipRepository)
    {
        $this->repository = $petRelationshipRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'commitment' => [ 'r.commitment' => 'desc' ],
            ],
            [
                'pet' => [ $this, 'filterPet' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('r')
            ->join('r.relationship', 'friend')
        ;
    }

    public function filterPet(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('r.pet = :pet')
            ->setParameter('pet', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
