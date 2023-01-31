<?php
namespace App\Service\Filter;

use App\Repository\UserActivityLogRepository;
use Doctrine\ORM\QueryBuilder;

class UserActivityLogsFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(UserActivityLogRepository $petActivityLogRepository)
    {
        $this->repository = $petActivityLogRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'l.id' => 'desc' ], // first one is the default
            ],
            [
                'tags' => [ $this, 'filterTags' ],
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l');
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('l.user=:userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterTags(QueryBuilder $qb, $value)
    {
        if(!in_array('tags', $qb->getAllAliases()))
            $qb->leftJoin('l.tags', 'tags');

        if(is_array($value))
            $qb->andWhere('tags.title IN (:tags)');
        else
            $qb->andWhere('tags.title=:tags');

        $qb->setParameter('tags', $value);
    }
}
