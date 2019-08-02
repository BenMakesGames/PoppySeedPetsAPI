<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\ParkEventRepository;
use Doctrine\ORM\QueryBuilder;

class ParkEventHistoryFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    /**
     * @var User
     */
    private $user;

    private $repository;

    public function __construct(ParkEventRepository $parkEventRepository)
    {
        $this->repository = $parkEventRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'e.id', 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->andWhere('p.owner = :user')
            ->setParameter('user', $this->user->getId())
        ;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

}