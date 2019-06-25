<?php
namespace App\Service\Filter;

use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

class UserFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    public function __construct(UserRepository $userRepository)
    {
        $this->filterer = new Filterer(
            $userRepository, 'u',
            self::PAGE_SIZE,
            [
                'lastActivity' => [ 'u.lastActivity', 'desc' ], // first one is the default
                'registeredOn' => [ 'u.registeredOn', 'asc' ],
                'name' => [ 'u.name', 'asc' ],
                'id' => [ 'u.id', 'asc' ],
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