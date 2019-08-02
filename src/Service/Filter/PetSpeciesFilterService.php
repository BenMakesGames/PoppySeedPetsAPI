<?php
namespace App\Service\Filter;

use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(PetSpeciesRepository $petSpeciesRepository)
    {
        $this->repository = $petSpeciesRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'i.name' => 'asc' ], // first one is the default
                'id' => [ 'i.id' => 'asc' ],
            ],
            [
                'name' => array($this, 'filterName'),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i');
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }
}