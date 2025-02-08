<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\UserLetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserLetterFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserLetter::class);

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
