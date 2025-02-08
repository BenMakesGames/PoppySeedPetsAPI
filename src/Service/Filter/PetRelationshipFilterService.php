<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\PetRelationship;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class PetRelationshipFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private readonly ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(PetRelationship::class, 'readonly');

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
            ->setParameter('pet', $value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
