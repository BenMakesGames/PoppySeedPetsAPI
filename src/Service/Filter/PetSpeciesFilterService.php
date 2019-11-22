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
                'name' => [ $this, 'filterName' ],
                'classification' => [ $this, 'filterClassification' ],
                'canTransmigrate' => [ $this, 'filterCanTransmigrate' ],
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

    public function filterClassification(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.image LIKE :imageLike')
            ->setParameter('imageLike', $value . '/%')
        ;
    }

    public function filterCanTransmigrate(QueryBuilder $qb, $value)
    {
        if($value)
        {
            $qb
                ->andWhere('(i.availableAtSignup=1 OR i.availableFromBreeding=1 OR i.availableFromPetShelter=1)')
            ;
        }
        else
        {
            $qb
                ->andWhere('i.availableAtSignup=0 AND i.availableFromBreeding=0 AND i.availableFromPetShelter=0')
            ;
        }
    }
}