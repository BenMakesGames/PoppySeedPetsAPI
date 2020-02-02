<?php
namespace App\Service\Filter;

use App\Repository\PetRepository;
use Doctrine\ORM\QueryBuilder;

class PetFilterService
{
    use FilterService;

    public const PAGE_SIZE = 12;

    private $repository;

    public function __construct(PetRepository $petRepository)
    {
        $this->repository = $petRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'p.id' => 'asc' ],
            ],
            [
                'owner' => [ $this, 'filterOwner' ],
                'inDaycare' => [ $this, 'filterInDaycare' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p');
    }

    public function filterOwner(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('p.owner = :userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterInDaycare(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('p.inDaycare = 0');
        else
            $qb->andWhere('p.inDaycare = 1');
    }
}
