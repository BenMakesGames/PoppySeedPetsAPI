<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Entity\UserFriend;
use App\Repository\ItemRepository;
use App\Repository\KnownRecipesRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class DaycareFilterService
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
                'id' => [ 'p.id' => 'ASC' ], // first one is the default
            ],
            [
                'user' => [ $this, 'filterUser' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p')
            ->andWhere('p.inDaycare=1')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('p.owner=:owner')
            ->setParameter('owner', $value)
        ;
    }
}