<?php
namespace App\Service\Filter;

use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    public function __construct(PetSpeciesRepository $petSpeciesRepository)
    {
        $this->filterer = new Filterer(
            $petSpeciesRepository, 'i',
            self::PAGE_SIZE,
            [
                'name' => 'i.name', // first one is the default
                'id' => 'i.id',
            ],
            [
                'name' => array($this, 'filterName'),
            ]
        );
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }
}