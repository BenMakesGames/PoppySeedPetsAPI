<?php
namespace App\Service\Filter;

use App\Enum\ParkEventTypeEnum;
use App\Repository\ArticleRepository;
use App\Repository\ParkEventRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ParkEventFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    private $repository;

    public function __construct(ParkEventRepository $parkEventRepository)
    {
        $this->repository = $parkEventRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'a.id', 'asc' ], // first one is the default
            ],
            [
                'isOpen' => [ $this, 'filterIsOpen' ],
                'type' => [ $this, 'filterType' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e');
    }

    public function filterIsOpen(QueryBuilder $qb, $value)
    {
        $qb->andWhere('e.isFull!=:isOpen')->setParameter('isOpen', boolval($value));
    }

    public function filterType(QueryBuilder $qb, $value)
    {
        if(!ParkEventTypeEnum::isAValue($value))
            throw new UnprocessableEntityHttpException('"' . $value . '" is not a valid type.');

        $qb->andWhere('e.type=:type')->setParameter('type', $value);
    }
}