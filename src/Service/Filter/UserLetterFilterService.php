<?php
namespace App\Service\Filter;

use App\Repository\UserLetterRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserLetterFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(UserLetterRepository $userLetterRepository)
    {
        $this->repository = $userLetterRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'receivedon' => [ 'l.receivedOn' => 'desc' ], // first one is the default
            ],
            [
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l')
            ->leftJoin('l.letter', 'letter')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('l.user = :userId')
            ->setParameter('userId', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
