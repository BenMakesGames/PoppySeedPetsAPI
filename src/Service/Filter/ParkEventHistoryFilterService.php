<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\ParkEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ParkEventHistoryFilterService
{
    use FilterService;

    public const PageSize = 20;

    private ?User $user;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(ParkEvent::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'id' => [ 'e.id' => 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        if(!$this->user) throw new \Exception('User not set.');

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

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}