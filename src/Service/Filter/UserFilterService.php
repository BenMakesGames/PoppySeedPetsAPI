<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    /**
     * @var User|null
     */
    private $user;

    private $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'lastactivity' => [ 'u.lastActivity' => 'desc' ], // first one is the default
                'registeredon' => [ 'u.registeredOn' => 'asc' ],
                'name' => [ 'u.name' => 'asc' ],
                'id' => [ 'u.id' => 'asc' ],
            ],
            [
                'name' => [ $this, 'filterName' ],
                'followedBy' => [ $this, 'filterFollowedBy' ],
                'following' => [ $this, 'filterFollowing' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('u');
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('u.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterFollowedBy(QueryBuilder $qb, $value)
    {
        if($this->user && ($value === $this->user->getId() || $this->user->hasRole('ROLE_ADMIN')))
        {
            if(!in_array('f', $qb->getAllAliases()))
                $qb->leftJoin('u.followedBy', 'f');

            $qb
                ->andWhere('f.user = :followedBy')
                ->setParameter('followedBy', $value)
            ;
        }
    }

    public function filterFollowing(QueryBuilder $qb, $value)
    {
        if($this->user && ($value === $this->user->getId() || $this->user->hasRole('ROLE_ADMIN')))
        {
            if(!in_array('g', $qb->getAllAliases()))
                $qb->leftJoin('u.following', 'g');

            $qb
                ->andWhere('g.following = :following')
                ->setParameter('following', $value)
            ;
        }
    }
}
