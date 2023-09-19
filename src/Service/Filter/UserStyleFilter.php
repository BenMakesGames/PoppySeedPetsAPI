<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Entity\UserStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserStyleFilter
{
    use FilterService;

    public const PAGE_SIZE = 12;

    /**
     * @var User
     */
    private $user;

    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserStyle::class);

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'user' => [ 'themeOwner.name' => 'asc', 's.id' => 'desc' ], // first one is the default
            ],
            [
                'following' => [ $this, 'filterFollowing' ],
            ]
        );
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('s')
            ->andWhere('s.name != :current')
            ->setParameter('current', UserStyle::CURRENT)
            ->join('s.user', 'themeOwner')
        ;
    }

    public function filterFollowing(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'true' || (int)$value > 0)
        {
            return $qb
                ->join('themeOwner.followedBy', 'followedBy')
                ->andWhere('followedBy.user=:currentUser')
                ->setParameter('currentUser', $this->user)
            ;
        }

        return $qb;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}