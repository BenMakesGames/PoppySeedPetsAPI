<?php
namespace App\Service\Filter;

use App\Repository\DevTaskRepository;
use Doctrine\ORM\QueryBuilder;

class DevTaskFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(DevTaskRepository $devTaskRepository)
    {
        $this->repository = $devTaskRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 't.id' => 'asc' ], // first one is the default
                'releasedon' => [ 't.releasedOn' => 'desc', 't.id' => 'desc' ],
            ],
            [
                'text' => [ $this, 'filterText' ],
                'type' => [ $this, 'filterType' ],
                'status' => [ $this, 'filterStatus' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('t');
    }

    public function filterText(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('(t.title LIKE :textLike OR t.description LIKE :textLike)')
            ->setParameter('textLike', '%' . $value . '%')
        ;
    }

    public function filterType(QueryBuilder $qb, $value)
    {
        if(!is_array($value))
            $qb->andWhere('t.type = :type');
        else
            $qb->andWhere('t.type IN (:type)');

        $qb->setParameter('type', $value);
    }

    public function filterStatus(QueryBuilder $qb, $value)
    {
        if(!is_array($value))
            $qb->andWhere('t.status = :status');
        else
            $qb->andWhere('t.status IN (:status)');

        $qb->setParameter('status', $value);
    }
}