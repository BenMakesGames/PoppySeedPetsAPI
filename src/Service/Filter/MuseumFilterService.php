<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Entity\UserFriend;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MuseumFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    public function __construct(MuseumItemRepository $museumItemRepository)
    {
        $this->filterer = new Filterer(
            $museumItemRepository, 'm',
            self::PAGE_SIZE,
            [
                'donatedOn' => [ 'm.donatedOn', 'desc' ], // first one is the default
                'itemName' => [ 'm.item.name', 'asc' ],
            ],
            [
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $value)
        ;
    }
}