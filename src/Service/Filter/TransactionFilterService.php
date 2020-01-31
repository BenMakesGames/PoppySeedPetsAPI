<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\TransactionHistoryRepository;
use Doctrine\ORM\QueryBuilder;

class TransactionFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    /**
     * @var User
     */
    private $user;

    private $repository;

    public function __construct(TransactionHistoryRepository $transactionHistoryRepository)
    {
        $this->repository = $transactionHistoryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 't.id' => 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $this->user->getId())
        ;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}