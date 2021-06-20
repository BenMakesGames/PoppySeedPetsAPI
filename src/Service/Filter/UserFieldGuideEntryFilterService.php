<?php
namespace App\Service\Filter;

use App\Repository\UserFieldGuideEntryRepository;
use Doctrine\ORM\QueryBuilder;

class UserFieldGuideEntryFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(UserFieldGuideEntryRepository $userFieldGuideEntryRepository)
    {
        $this->repository = $userFieldGuideEntryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'entry.name' => 'asc' ],
                'discoveredon' => [ 'u.discoveredOn' => 'desc' ],
            ],
            [
                'user' => [ $this, 'filterUser' ],
                'name' => [ $this, 'filterName' ],
                'type' => [ $this, 'filterType' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('u')
            ->join('u.entry', 'entry')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('u.user = :userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('entry.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterType(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('entry.type = :entryType')
            ->setParameter('entryType', $value)
        ;
    }
}
