<?php
namespace App\Service\Filter;

use App\Repository\MuseumItemRepository;
use App\Repository\PetGroupRepository;
use Doctrine\ORM\QueryBuilder;

class PetGroupFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(PetGroupRepository $petGroupRepository)
    {
        $this->repository = $petGroupRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'lastMetOn' => [ 'g.lastMetOn' => 'desc' ], // first one is the default
                'createdOn' => [ 'g.createdOn' => 'desc' ],
                'name' => [ 'g.name' => 'asc' ],
            ],
            [
                'type' => [ $this, 'filterType' ],
                'withPetsOwnedBy' => [ $this, 'filterWithPetsOwnedBy' ],
                'name' => [ $this, 'filterName' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('g');
    }

    public function filterType(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('g.type = :type')
            ->setParameter('type', $value)
        ;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('g.name LIKE :name')
            ->setParameter('name', '%' . $value . '%')
        ;
    }

    public function filterWithPetsOwnedBy(QueryBuilder $qb, $value)
    {
        if(is_numeric($value) && (int)$value == $value)
        {
            if(!in_array('p', $qb->getAllAliases()))
                $qb->leftJoin('g.members', 'p');
            
            $qb
                ->andWhere('p.owner=:owner')
                ->setParameter('owner', $value)
            ;
        }
    }
}
