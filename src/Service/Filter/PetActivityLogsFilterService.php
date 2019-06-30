<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Entity\UserFriend;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PetActivityLogsFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    public function __construct(PetActivityLogRepository $petActivityLogRepository)
    {
        $this->filterer = new Filterer(
            $petActivityLogRepository, 'l',
            self::PAGE_SIZE,
            [
                'id' => [ 'l.id', 'desc' ], // first one is the default
            ],
            [
                'pet' => [ $this, 'filterPet' ],
            ]
        );
    }

    public function filterPet(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('l.pet = :pet')
            ->setParameter('pet', $value)
        ;
    }
}